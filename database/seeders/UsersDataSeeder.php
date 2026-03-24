<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class UsersDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');
        $departments = DB::table('departments')->pluck('id')->toArray();
        $shifts = DB::table('shifts')->pluck('id')->toArray();

        if (empty($departments) || empty($shifts)) {
            return; // Precaución si no existen dptos o turnos
        }

        $usersToInsert = [];

        for ($i = 0; $i < 50; $i++) {
            $id = 'qr-' . Str::random(7);
            
            $usersToInsert[] = [
                'id' => $id,
                'name' => $faker->firstName,
                'last_name' => $faker->lastName,
                'age' => $faker->numberBetween(18, 60),
                'gender' => $faker->randomElement(['Masculino', 'Femenino', 'Otro']),
                'email' => $faker->unique()->safeEmail,
                'address' => $faker->address,
                'phone_number' => $faker->numerify('########'), // String de 8 digitos
                'profile_image' => 'default_profile.png',
                'qr_image' => $id . '.png',
                'shift_id' => $faker->randomElement($shifts),
                'department_id' => $faker->randomElement($departments),
                'status' => $faker->randomElement(['Activo', 'Activo', 'Activo', 'Inactivo']),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('users')->insert($usersToInsert);
    }
}
