<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            ['name' => 'Recursos Humanos', 'description' => 'Área de gestión de personal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Desarrollo de Software', 'description' => 'Equipo de ingeniería', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Ventas', 'description' => 'Equipo comercial', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Soporte Técnico', 'description' => 'Atención al cliente y fallos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Logística', 'description' => 'Envíos y empaquetado', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
