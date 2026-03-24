<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Users;
use App\Models\Events;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Services\AttendanceReportService;

class AttendanceController extends Controller
{
    /**
     * Registra el ingreso o egreso de asistencia de un empleado para el evento activo de la fecha actual.
     */
    public function attachAttendance(Request $request, string $id)
    {
        $user = Users::find($id);

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        if ($user->status === "Inactivo") {
            return $this->errorResponse('Inactive user cannot register attendance', 403);
        }

        $active = Events::where('status', true)->first();
        if (!$active) {
            return $this->errorResponse('No active events found', 404);
        }

        $fechaActual = Carbon::now()->format('Y-m-d');
        $horaActual = Carbon::now()->format('H:i:s');
        $currentTime = Carbon::now();

        // Armar el cruce de asistencia de la base de datos
        $query = $user->events()->wherePivot('attendance_date', $fechaActual);
        
        // Si el evento no es el generalizado (daily), lo buscamos directo en su ID específico
        if (!$active->daily_attendance) {
            $query->wherePivot('event_id', $active->id);
        }

        $recentAttendance = $query->orderBy('entry_time', 'desc')->first();

        // == REGLA 1: Si no hay asistencia hoy, marcar ENTRADA
        if (!$recentAttendance) {
            try {
                $user->events()->attach($active->id, [
                    'entry_time' => $horaActual,
                    'attendance_date' => $fechaActual,
                ]);
                return $this->successResponse(null, 'Attendance entry registered successfully');
            } catch (\Exception $e) {
                return $this->errorResponse('Error registering attendance: ' . $e->getMessage(), 400);
            }
        }

        // == REGLA 2: Si ya marcó, validar la tolerancia en minutos para decidir si puede marcar SALIDA
        $entryTimePlusTolerance = Carbon::parse($recentAttendance->pivot->entry_time)->addMinutes($active->change_attendance);

        if ($entryTimePlusTolerance->greaterThan($currentTime)) {
            return $this->errorResponse('Attendance entry already registered', 409);
        }

        if ($recentAttendance->pivot->finish_time) {
            return $this->errorResponse('Attendance exit already registered', 409);
        }

        // == REGLA 3: Si se superó la tolerancia y no había salido, marcar SALIDA
        try {
            $user->events()
                 ->wherePivot('attendance_date', $fechaActual)
                 ->wherePivot('event_id', $recentAttendance->pivot->event_id)
                 ->updateExistingPivot($recentAttendance->pivot->event_id, [
                     'finish_time' => $horaActual,
                 ]);
          
            return $this->successResponse(null, 'Attendance exit registered successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error finalizing attendance: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Genera un reporte detallado de un usuario único dado su nombre o apellido.
     */
    public function generateReportUser(\App\Http\Requests\AttendanceReportRequest $request)
    {
        $user = Users::where(DB::raw("CONCAT(name, ' ', last_name)"), $request->name)->first();
       
        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        $event = Events::find($request->event_id);
    
        $startDate = Carbon::parse($request->initial_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
    
        $attendances = $user->events()
                            ->wherePivot('attendance_date', '>=', $startDate)
                            ->wherePivot('attendance_date', '<=', $endDate)
                            ->wherePivot('event_id', $event->id)
                            ->orderBy('attendance_date')
                            ->get();

        // Delega la compilación a la capa Service
        $fullAttendances = AttendanceReportService::buildReportCollection(
            $attendances, 
            $user->shift, 
            $event->daily_attendance, 
            $startDate, 
            $endDate,
            $user->id
        );

        $response = [
            'user_name' => $user->name . ' ' . $user->last_name,
            'shift_name' => $user->shift->name,
            'event_name' => $event->name,
            'department_name' => $user->department->name,
            'daily_attendance' => $event->daily_attendance == 1 ? 'Si' : 'No',
            'total_dedicated_hours' => $fullAttendances->sum('time_dedicated'),
            'attendances' => $fullAttendances
        ];

        if ($event->daily_attendance == 1) {
            $response['total_non_dedicated_time'] = $fullAttendances->sum('time_non_dedicated');
        }

        return $this->successResponse($response, 'Report generated successfully');
    }

    /**
     * Obtiene el listado rápido de la asistencia general agrupada por el día en curso en el dashboard.
     */
    public function getDailyAttendace()
    {
        $active = Events::where('status', true)->first();
        if (!$active) {
            return $this->errorResponse('No active events found', 400);
        }

        $fechaActual = Carbon::now()->format('Y-m-d');

        // Obtener las asistencias del evento activo para la fecha actual
        $recentAttendance = $active->users()
            ->wherePivot('attendance_date', $fechaActual)
            ->orderBy('pivot_entry_time', 'desc')
            ->get();

        $attendanceData = $recentAttendance->map(function ($user) {
            return [
                'user_name' => $user->name. ' '. $user->last_name ,
                'entry_time' => $user->pivot->entry_time,
                'finish_time' => $user->pivot->finish_time,
                'attendance_date' => $user->pivot->attendance_date,
            ];
        });

        return $this->successResponse([
            'attendance' => $attendanceData,
            'total_records' => $attendanceData->count(),
            'active_users_count' => Users::where('status', 'Activo')->count(),
            'inactive_users_count' => Users::where('status', 'Inactivo')->count()
        ], 'Attendances retrieved successfully');
    }

    /**
     * Genera un reporte masivo departamental sobre los usuarios que pasaron su umbral de tolerancia.
     */
    public function generateReportUsers(\App\Http\Requests\MassAttendanceReportRequest $request)
    {
        $event = Events::find($request->event_id);
        $startDate = Carbon::parse($request->initial_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        // Optimizamos cargando usuarios con sus eventos (asistencias) filtrados por rango en una sola consulta
        $usersQuery = Users::with(['shift', 'department', 'events' => function($query) use ($startDate, $endDate, $event) {
            $query->wherePivot('attendance_date', '>=', $startDate)
                  ->wherePivot('attendance_date', '<=', $endDate)
                  ->wherePivot('event_id', $event->id)
                  ->orderBy('attendance_date');
        }]);

        if ($request->department_id != 0) {
            $usersQuery->where('department_id', $request->department_id);
        }
        
        $users = $usersQuery->get();

        if ($users->isEmpty()) {
            return $this->errorResponse('No users found', 404);
        }
        
        $result = [];

        foreach ($users as $user) {
            // El N+1 se soluciona porque $user->events ya está cargado
            $attendances = $user->events;

            // Delega la compilación a la capa Service
            $fullAttendances = AttendanceReportService::buildReportCollection(
                $attendances, 
                $user->shift, 
                $event->daily_attendance, 
                $startDate, 
                $endDate,
                $user->id
            );

            $totalDedicatedTime = $fullAttendances->sum('time_dedicated');

            if ($event->daily_attendance == 1) {
                $totalNonDedicatedTime = $fullAttendances->sum('time_non_dedicated');  
                
                // Usamos la columna corregida ya que la migración se ejecutará
                $shift_monthly_late_allowance = $user->shift->monthly_late_allowance;

                if ($totalNonDedicatedTime > $shift_monthly_late_allowance) {
                    $result[] = [
                        'user_name' => $user->name . ' ' . $user->last_name,
                        'shift_name' => $user->shift->name,
                        'event_name' => $event->name,
                        'department_name' => $user->department->name,
                        'monthly_late_allowance' => $shift_monthly_late_allowance, 
                        'daily_attendance' => 'Si',
                        'total_non_dedicated_time' => $totalNonDedicatedTime,
                        'total_dedicated_hours' => $totalDedicatedTime,
                        'attendances' => $fullAttendances->filter(function ($att) {
                            return ($att['time_non_dedicated'] ?? 0) > 0;
                        })->values()
                    ];
                }
            } else {
                $result[] = [
                    'daily_attendance' => 'No',
                    'user_name' => $user->name . ' ' . $user->last_name,
                    'department_name' => $user->department->name,
                    'event_name' => $event->name,
                    'total_dedicated_hours' => $totalDedicatedTime,
                ];
            }                     
        }

        return $this->successResponse($result, 'General report generated successfully');
    }
}
