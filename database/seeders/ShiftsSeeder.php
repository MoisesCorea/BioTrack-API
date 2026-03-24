<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('shifts')->insert([
            [
                'name' => 'Matutino',
                'entry_time' => '08:00:00',
                'finish_time' => '17:00:00',
                'shift_duration' => 540,
                'mothly_late_allowance' => 120, // 2 horas al mes
                'days' => json_encode([1, 2, 3, 4, 5]), // Lun a Vie
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Vespertino',
                'entry_time' => '14:00:00',
                'finish_time' => '22:00:00',
                'shift_duration' => 480,
                'mothly_late_allowance' => 120,
                'days' => json_encode([1, 2, 3, 4, 5]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Nocturno',
                'entry_time' => '22:00:00',
                'finish_time' => '06:00:00',
                'shift_duration' => 480,
                'mothly_late_allowance' => 60,
                'days' => json_encode([1, 2, 3, 4, 5]),
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
