<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('events')->insert([
            [
                'name' => 'Jornada Laboral Diaria',
                'status' => true, // Activo
                'change_attendance' => 240, // 4 horas en minutos
                'daily_attendance' => true,
                'description' => 'Control de asistencia diario de todo el personal',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Evento Pasado (Seminario)',
                'status' => false, // Inactivo
                'change_attendance' => 120,
                'daily_attendance' => false,
                'description' => 'Asistencia única para evento mensual',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
