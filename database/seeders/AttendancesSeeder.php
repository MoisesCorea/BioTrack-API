<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendancesSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->where('status', 'Activo')->get();
        $event = DB::table('events')->where('status', true)->first();

        if ($users->isEmpty() || !$event) {
            return;
        }

        $attendancesToInsert = [];
        $today = Carbon::now();

        foreach ($users as $user) {
            $shift = DB::table('shifts')->where('id', $user->shift_id)->first();
            if (!$shift) continue;

            $workingDays = json_decode($shift->days, true) ?? [];

            // Generar asistencia para los últimos 15 días
            for ($i = 15; $i >= 0; $i--) {
                $date = $today->copy()->subDays($i);

                // Si hoy no es día laboral en su turno, saltar (random) o simplemente crearla
                if (!in_array($date->dayOfWeek, $workingDays)) {
                    continue; // No trabajó ese día
                }

                // Simular ausencias aleatorias (ej: 10% de probabilidad)
                if (rand(1, 100) > 90) continue;

                $shiftEntry = Carbon::createFromTimeString($shift->entry_time);
                $shiftFinish = Carbon::createFromTimeString($shift->finish_time);

                // Simular hora de entrada (puede llegar temprano o tarde hasta 60 min)
                $entryTime = $shiftEntry->copy()->addMinutes(rand(-20, 60));

                // Simular hora de salida (puede irse antes o después)
                // 5% de probabilidad de que se olvide de marcar la salida
                if (rand(1, 100) > 5) {
                    $finishTime = $shiftFinish->copy()->addMinutes(rand(-15, 45))->format('H:i:s');
                } else {
                    $finishTime = null; // No marcó salida
                }

                $attendancesToInsert[] = [
                    'user_id' => $user->id,
                    'event_id' => $event->id,
                    'entry_time' => $entryTime->format('H:i:s'),
                    'finish_time' => $finishTime,
                    'attendance_date' => $date->format('Y-m-d')
                ];
            }
        }

        // Insertar en bloques para no sobrecargar la memoria
        $chunks = array_chunk($attendancesToInsert, 500);
        foreach ($chunks as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
    }
}
