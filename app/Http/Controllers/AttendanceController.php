<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Users;
use App\Models\Events;
use Carbon\Carbon;
use App\Services\AttendanceReportService;
use App\Http\Requests\AttendanceReportRequest;
use App\Http\Requests\MassAttendanceReportRequest;

class AttendanceController extends Controller
{
    /**
     * Registra el ingreso o egreso de asistencia de un empleado para el evento activo.
     * Soporta turnos nocturnos mediante una ventana de 14 horas.
     */
    public function attachAttendance(Request $request, string $id)
    {
        $user = Users::find($id);

        if (!$user) return $this->errorResponse('User not found', 404);
        if ($user->status === "Inactivo") return $this->errorResponse('User is inactive', 403);

        $active = Events::where('status', true)->first();
        if (!$active) return $this->errorResponse('No active events found', 404);

        $now          = Carbon::now();
        $fechaActual  = $now->format('Y-m-d');
        $horaActual   = $now->format('H:i:s');

        // Inteligencia para turnos nocturnos:
        // Si el turno del empleado ESTÁ DISEÑADO para empezar en la tarde/noche (>= 12pm)
        // pero él está escaneando su QR en la madrugada de hoy (< 12am)...
        // Significa que viene llegando tarde a su turno de AYER. 
        if ($user->shift) {
            $shiftEntryHour = (int) Carbon::parse($user->shift->entry_time)->format('H');
            if ($shiftEntryHour >= 12 && $now->hour < 12) {
                $fechaActual = $now->copy()->subDay()->format('Y-m-d');
            }
        }

        // LÓGICA DE VENTANA: Buscamos una asistencia sin salida en las últimas 14 horas
        // Esto soporta turnos nocturnos que cruzan la medianoche.
        $query = $user->events()
            ->wherePivot('event_id', $active->id)
            ->wherePivotNull('finish_time')
            ->wherePivot('attendance_date', '>=', $now->copy()->subHours(14)->format('Y-m-d'))
            ->orderBy('pivot_entry_time', 'desc');

        $openAttendance = $query->first();

        // CASO 1: No hay asistencia abierta → REGISTRAR ENTRADA
        if (!$openAttendance) {
            try {
                $user->events()->attach($active->id, [
                    'entry_time'      => $horaActual,
                    'attendance_date' => $fechaActual,
                ]);
                return $this->successResponse(null, 'Attendance entry registered successfully');
            } catch (\Exception $e) {
                return $this->errorResponse('Error registering attendance: ' . $e->getMessage(), 400);
            }
        }

        // CASO 2: Hay asistencia abierta → Validar tolerancia y REGISTRAR SALIDA
        $entryTimestamp = Carbon::parse(
            $openAttendance->pivot->attendance_date . ' ' . $openAttendance->pivot->entry_time
        );

        if ($entryTimestamp->diffInMinutes($now) < $active->change_attendance) {
            return $this->errorResponse('Too soon to register exit. Please wait.', 409);
        }

        try {
            $user->events()
                 ->wherePivot('attendance_date', $openAttendance->pivot->attendance_date)
                 ->wherePivot('event_id', $active->id)
                 ->updateExistingPivot($active->id, [
                     'finish_time' => $horaActual,
                 ]);

            return $this->successResponse(null, 'Attendance exit registered successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error finalizing attendance: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Obtiene el listado de asistencias del día en curso para el dashboard.
     */
    public function getDailyAttendace()
    {
        $active = Events::where('status', true)->first();
        if (!$active) return $this->errorResponse('No active events found', 400);

        $fechaActual = Carbon::now()->format('Y-m-d');

        $recentAttendance = $active->users()
            ->wherePivot('attendance_date', $fechaActual)
            ->orderBy('pivot_entry_time', 'desc')
            ->get();

        $attendanceData = $recentAttendance->map(function ($user) {
            return [
                'user_name'       => $user->name . ' ' . $user->last_name,
                'entry_time'      => $user->pivot->entry_time,
                'finish_time'     => $user->pivot->finish_time,
                'attendance_date' => $user->pivot->attendance_date,
            ];
        });

        return $this->successResponse([
            'attendance'          => $attendanceData,
            'total_records'       => $attendanceData->count(),
            'active_users_count'  => Users::where('status', 'Activo')->count(),
            'inactive_users_count'=> Users::where('status', 'Inactivo')->count(),
        ], 'Attendances retrieved successfully');
    }

    /**
     * Reporte detallado de un usuario único (búsqueda por nombre completo).
     */
    public function generateReportUser(AttendanceReportRequest $request)
    {
        $user = Users::where(DB::raw("CONCAT(name, ' ', last_name)"), $request->name)
                     ->with(['shift', 'department'])
                     ->first();

        if (!$user) return $this->errorResponse('User not found.', 404);

        $event     = Events::findOrFail($request->event_id);
        $startDate = $request->initial_date;
        $endDate   = $request->end_date;

        $attendances = $user->events()
                            ->wherePivot('attendance_date', '>=', $startDate)
                            ->wherePivot('attendance_date', '<=', $endDate)
                            ->wherePivot('event_id', $event->id)
                            ->orderBy('attendance_date')
                            ->get();

        $fullAttendances = AttendanceReportService::buildReportCollection(
            $attendances,
            $user->shift,
            (int) $event->daily_attendance,
            $startDate,
            $endDate,
            $user->id
        );

        $response = [
            'user_name'           => $user->name . ' ' . $user->last_name,
            'shift_name'          => $user->shift->name,
            'event_name'          => $event->name,
            'department_name'     => $user->department->name,
            'daily_attendance'    => $event->daily_attendance == 1 ? 'Si' : 'No',
            'total_dedicated_time' => $fullAttendances->sum('time_dedicated'),
            'total_overtime_minutes' => $fullAttendances->sum('overtime_minutes'), 
        ];

        if ($event->daily_attendance == 1) {
            $allowance = $user->shift->monthly_late_allowance ?? 0;
            $rawNonDedicated = $fullAttendances->sum('time_non_dedicated');
            
            $response['monthly_late_allowance'] = $allowance;
            $response['raw_non_dedicated_time'] = $rawNonDedicated;
            
            // Si supera el umbral, se penaliza todo integro. Si no, se perdona (0)
            $response['total_non_dedicated_time'] = ($rawNonDedicated > $allowance) ? $rawNonDedicated : 0;
        }

        $response['attendances'] = $fullAttendances;

        return $this->successResponse($response, 'Individual report generated successfully');
    }

    /**
     * Reporte masivo departamental. En modo diario filtra usuarios que superaron
     * el umbral de tolerancia mensual; en modo evento lista todo el departamento.
     */
    public function generateReportUsers(MassAttendanceReportRequest $request)
    {
        $event     = Events::findOrFail($request->event_id);
        $startDate = $request->initial_date;
        $endDate   = $request->end_date;

        // Eager Loading para evitar N+1
        $usersQuery = Users::with(['shift', 'department', 'events' => function ($query) use ($startDate, $endDate, $event) {
            $query->wherePivot('attendance_date', '>=', $startDate)
                  ->wherePivot('attendance_date', '<=', $endDate)
                  ->wherePivot('event_id', $event->id)
                  ->orderBy('attendance_date');
        }])->where('status', 'Activo');

        if ($request->department_id != 0) {
            $usersQuery->where('department_id', $request->department_id);
        }

        $users = $usersQuery->get();

        if ($users->isEmpty()) {
            return $this->errorResponse('No users found', 404);
        }

        $result = [];

        foreach ($users as $user) {
            $fullAttendances = AttendanceReportService::buildReportCollection(
                $user->events,
                $user->shift,
                (int) $event->daily_attendance,
                $startDate,
                $endDate,
                $user->id
            );

            $totalDedicatedTime = $fullAttendances->sum('time_dedicated');

            if ($event->daily_attendance == 1) {
                $totalNonDedicatedTime      = $fullAttendances->sum('time_non_dedicated');
                $shift_monthly_late_allowance = $user->shift->monthly_late_allowance;

                // Solo incluimos al usuario si superó el umbral de tolerancia
                if ($totalNonDedicatedTime > $shift_monthly_late_allowance) {
                    $result[] = [
                        'user_name'              => $user->name . ' ' . $user->last_name,
                        'shift_name'             => $user->shift->name,
                        'event_name'             => $event->name,
                        'department_name'        => $user->department->name,
                        'daily_attendance'       => 'Si',
                        'total_dedicated_time'   => $totalDedicatedTime,
                        'total_overtime_minutes' => $fullAttendances->sum('overtime_minutes'),
                        'monthly_late_allowance' => $shift_monthly_late_allowance,
                        'raw_non_dedicated_time' => $totalNonDedicatedTime,
                        'total_non_dedicated_time'=> $totalNonDedicatedTime, // Aquí siempre se cobra todo porque ya superó el umbral
                        'attendances'            => $fullAttendances->filter(function ($att) {
                            return ($att['time_non_dedicated'] ?? 0) > 0;
                        })->values(),
                    ];
                }
            } else {
                $result[] = [
                    'user_name'           => $user->name . ' ' . $user->last_name,
                    'shift_name'          => $user->shift->name,
                    'event_name'          => $event->name,
                    'department_name'     => $user->department->name,
                    'total_dedicated_time' => $totalDedicatedTime,
                    'total_overtime_minutes' => $fullAttendances->sum('overtime_minutes'),
                    'attendances'         => $fullAttendances,
                ];
            }
        }

        return $this->successResponse($result, 'Mass report generated successfully');
    }

    
   
}