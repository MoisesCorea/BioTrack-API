<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Models\Justification;

class AttendanceReportService
{
    public static function buildReportCollection(Collection $attendances, $shift, int $isDailyAttendance, string $startDate, string $endDate, string $userId): Collection
    {
        // Normalizamos duración y horarios
        $shiftDuration = ($shift->shift_duration <= 24) ? $shift->shift_duration * 60 : (int)$shift->shift_duration;
        $shiftEntry = Carbon::parse($shift->entry_time);
        $shiftFinish = Carbon::parse($shift->finish_time);
        $workingDays = json_decode($shift->days, true) ?? [];

        $processedAttendances = $attendances->map(function ($event) use ($shiftEntry, $shiftFinish, $isDailyAttendance, $shiftDuration) {
            $entry = Carbon::parse($event->pivot->entry_time);
            $finish = $event->pivot->finish_time ? Carbon::parse($event->pivot->finish_time) : null;
            $attDate = $event->pivot->attendance_date;

            // Construir Fechas ESPERADAS completas basadas en el attendance_date real del turno
            $expectedEntry = Carbon::parse($attDate . ' ' . $shiftEntry->format('H:i:s'));
            $expectedFinish = Carbon::parse($attDate . ' ' . $shiftFinish->format('H:i:s'));
            
            // Si el turno cruza la medianoche, la salida esperada es al día siguiente
            if ($expectedFinish < $expectedEntry) {
                $expectedFinish->addDay();
            }

            // Construir Fechas REALES completas
            $entryFull = Carbon::parse($attDate . ' ' . $entry->format('H:i:s'));
            
            // Si es turno nocturno y entró ANTES de la medianoche pero salió DESPUÉS
            if ($finish) {
                $finishFull = Carbon::parse($attDate . ' ' . $finish->format('H:i:s'));
                if ($expectedFinish > $expectedEntry && $finishFull < $entryFull && $entryFull >= $expectedEntry->copy()->subHours(2)) {
                    // Turno que cruza medianoche, ajustar día de salida si el finish es menor al entry
                    $finishFull->addDay();
                } else if ($expectedFinish < $expectedEntry) {
                    // Turno nocturno oficial, si marca salida en la madrugada es al día siguiente
                    if ($finishFull->format('H:i:s') < $entryFull->format('H:i:s') || $finishFull->format('H') < 12) {
                        $finishFull->addDay();
                    }
                }
            } else {
                $finishFull = null;
            }

            // Cálculo base en minutos
            $dedicatedMinutes = $finishFull ? $entryFull->diffInMinutes($finishFull) : 0;
            
            // Horas Extras
            $overtimeMinutes = ($dedicatedMinutes > $shiftDuration) ? ($dedicatedMinutes - $shiftDuration) : 0;

            if ($isDailyAttendance === 1) {
                // Tardanza: diferencia entre hora esperada y hora real (ignorando si llegó temprano usando max(0))
                $minutesLate = (int) max(0, $expectedEntry->diffInMinutes($entryFull, false));

                // Determinar si hay justificación para este día en particular (el controller la mandaría si fuera necesario, 
                // pero por ahora el modelo event no trae justificaciones. La buscaremos después, o aplicamos regla general acá).
                // Como las justificaciones se revisan después en el array, dejaremos un flag o aplicamos la penalización base acá.
                
                if ($finishFull) {
                    // Salida temprana
                    $minutesEarlyLeaving = (int) max(0, $finishFull->diffInMinutes($expectedFinish, false));
                    $timeNonDedicated = $minutesLate + $minutesEarlyLeaving;
                } else {
                    // "EL OLVIDADIZO": Entró pero no marcó salida.
                    $minutesEarlyLeaving = 0;
                    $timeNonDedicated = $minutesLate + ($shiftDuration / 2); // Tardanza + Medio turno de castigo
                }

                return [
                    'entry_time' => $event->pivot->entry_time,
                    'finish_time' => $finish ? $event->pivot->finish_time : "Sin marca",
                    'attendance_date' => $attDate,
                    'minutes_late' => $finishFull ? $minutesLate : "-", // Si olvidó salida, mostramos "-" para no confundir
                    'minutes_early_leaving' => $finishFull ? $minutesEarlyLeaving : "-",
                    'time_non_dedicated' => (int) $timeNonDedicated,
                    'time_dedicated' => $dedicatedMinutes,
                    'overtime_minutes' => $overtimeMinutes
                ];
            }

            return [
                'entry_time' => $event->pivot->entry_time,
                'finish_time' => $finish ? $event->pivot->finish_time : "Sin marca",
                'attendance_date' => $attDate,
                'time_dedicated' => $dedicatedMinutes,
                'overtime_minutes' => $overtimeMinutes
            ];
        });

        if ($isDailyAttendance === 1) {
            $report = collect();
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);

            // Eager loading de justificaciones (debería venir optimizado del controller)
            $justifications = Justification::where('user_id', $userId)
                ->where('status', 'Aprobado')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate]);
                })->get();

            for ($date = $start; $date->lte($end); $date->addDay()) {
                if (!in_array($date->dayOfWeek, $workingDays)) continue;

                $dateStr = $date->toDateString();
                $dayRecord = $processedAttendances->firstWhere('attendance_date', $dateStr);
                $just = $justifications->first(fn($j) => $dateStr >= $j->start_date && $dateStr <= $j->end_date);

                if ($dayRecord) {
                    // Si el usuario marcó asistencia pero tiene penalizaciones (llegó tarde o no marcó salida)
                    // y tiene una justificación aprobada para este día, le perdonamos el tiempo no dedicado.
                    if ($just && $dayRecord['time_non_dedicated'] > 0) {
                        $dayRecord['time_non_dedicated'] = 0;
                        $dayRecord['justification_type'] = $just->type;
                        $dayRecord['description'] = $just->description;
                    }
                    $report->push($dayRecord);
                } else {
                    if ($just) {
                        $report->push([
                            'entry_time' => 'Justificado',
                            'finish_time' => 'Justificado',
                            'attendance_date' => $dateStr,
                            'minutes_late' => 0,
                            'minutes_early_leaving' => 0,
                            'time_non_dedicated' => 0,
                            'time_dedicated' => $shiftDuration, // Justificado cuenta como laborado (minutos)
                            'justification_type' => $just->type,
                            'description' => $just->description,
                            'overtime_minutes' => 0
                        ]);
                    } else {
                        $report->push([
                            'entry_time' => 'Sin marca',
                            'finish_time' => 'Sin marca',
                            'attendance_date' => $dateStr,
                            'minutes_late' => '-',
                            'minutes_early_leaving' => '-',
                            'time_non_dedicated' => $shiftDuration,
                            'time_dedicated' => 0,
                            'overtime_minutes' => 0
                        ]);
                    }
                }
            }
            return $report;
        }

        return $processedAttendances;
    }
}