<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\Justification;

class AttendanceReportService
{
    /**
     * Calcula los tiempos dedicados, horas extras y penalizaciones de un usuario, basado en sus registros 
     * de asistencia guardados en BBDD y su turno asignado.
     * Retorna una colección formateada, y si el evento exige asistencia diaria, rellena los días ausentes.
     *
     * @param \Illuminate\Support\Collection $attendances  Asistencias crudas del modelo
     * @param \App\Models\Shifts $shift                    Turno del usuario
     * @param int $isDailyAttendance                       ¿El evento demanda asistencia diaria? (1 = Sí, 0 = No)
     * @param string $startDate                            Fecha inicio del rango
     * @param string $endDate                              Fecha fin del rango
     * @param string $userId                               ID del usuario
     * @return \Illuminate\Support\Collection
     */
    public static function buildReportCollection(Collection $attendances, $shift, int $isDailyAttendance, string $startDate, string $endDate, string $userId): Collection
    {
        $shift_entryTime = Carbon::parse($shift->entry_time);
        $shift_finishTime = Carbon::parse($shift->finish_time);
        
        // Obtener los días de trabajo del usuario basados en su turno
        $workingDays = json_decode($shift->days, true) ?? [];

        // Mapear los registros obtenidos de BBDD calculando penalizaciones según políticas del turno
        $processedAttendances = $attendances->map(function ($event) use ($shift_entryTime, $shift_finishTime, $isDailyAttendance) {
            $entryTime = Carbon::parse($event->pivot->entry_time);
            $finishTime = $event->pivot->finish_time ? Carbon::parse($event->pivot->finish_time) : null;

            // Calcular horas dedicadas generales
            $dedicatedMinutes = $finishTime ? $entryTime->diffInMinutes($finishTime) : 480; // Asumimos default 8 horas (480min)
            $dedicatedHours = $finishTime ? $dedicatedMinutes / 60 : 0;

            if ($isDailyAttendance === 1) {
                // Cálculo de penalizaciones de tiempo (Tardanzas y retiros antes de la meta de finalización)
                $minutesLate = intval($entryTime->greaterThan($shift_entryTime) ? $shift_entryTime->diffInMinutes($entryTime) : 0);
                $minutesEarlyLeaving = intval($finishTime && $finishTime->lessThan($shift_finishTime) ? $finishTime->diffInMinutes($shift_finishTime) : 0);
                
                $timeNonDedicated = $minutesLate + $minutesEarlyLeaving;

                return [
                    'entry_time' => $event->pivot->entry_time,
                    'finish_time' => $finishTime ? $event->pivot->finish_time : "Sin marca",
                    'attendance_date' => $event->pivot->attendance_date,
                    'minutes_late' => $minutesLate,
                    'minutes_early_leaving' => $minutesEarlyLeaving,
                    'time_non_dedicated' => $timeNonDedicated,
                    'time_dedicated' => round($dedicatedHours, 2),
                ];
            }

            // Para eventos sin asistencia diaria (libre asistencia)
            return [
                'entry_time' => $event->pivot->entry_time,
                'finish_time' => $finishTime ? $event->pivot->finish_time : "Sin marca",
                'attendance_date' => $event->pivot->attendance_date,
                'time_dedicated' => round($dedicatedHours, 2),
            ];
        });

        // Si el evento requiere asistencia diaria, rellenar de ceros o penalizaciones los días donde no llegó
        if ($isDailyAttendance === 1) {
            $dateRange = collect();
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            for ($date = $start; $date->lte($end); $date->addDay()) {
                // Agregar solo si el día en iteración cae dentro de los días laborales habituales
                if (in_array($date->dayOfWeek, $workingDays)) {
                    $dateRange->push($date->copy());
                }
            }

            // Obtener todas las justificaciones aprobadas del usuario en el rango una sola vez para no consultar en bucle
            $userJustifications = Justification::where('user_id', $userId)
                ->where('status', 'Aprobado')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate])
                          ->orWhereBetween('end_date', [$startDate, $endDate])
                          ->orWhere(function ($q) use ($startDate, $endDate) {
                              $q->where('start_date', '<=', $startDate)
                                ->where('end_date', '>=', $endDate);
                          });
                })->get();

            return $dateRange->map(function ($date) use ($processedAttendances, $userJustifications) {
                $attendance = $processedAttendances->firstWhere('attendance_date', $date->toDateString());

                if ($attendance) {
                    return $attendance;
                }

                $dateStr = $date->toDateString();

                // Verificar si el día está justificado
                $justification = $userJustifications->first(function ($j) use ($dateStr) {
                    return $dateStr >= $j->start_date && $dateStr <= $j->end_date;
                });

                if ($justification) {
                    return [
                        'entry_time' => 'Justificado',
                        'finish_time' => 'Justificado',
                        'attendance_date' => $dateStr,
                        'minutes_late' => 0,
                        'minutes_early_leaving' => 0,
                        'time_non_dedicated' => 0, // No hay penalización si está justificado
                        'time_dedicated' => 0,
                        'justification_type' => $justification->type,
                        'description' => $justification->description
                    ];
                }

                // Generar un registro fantasma "Sin Marca" si debía venir pero no se presentó (Falta completa)
                return [
                    'entry_time' => 'Sin marca',
                    'finish_time' => 'Sin marca',
                    'attendance_date' => $dateStr,
                    'minutes_late' => '-',
                    'minutes_early_leaving' => '-',
                    'time_non_dedicated' => 480, // Jornada completa fallada equivale a 480 min de penalización
                    'time_dedicated' => 0,
                ];
            });
        }

        return $processedAttendances;
    }
}
