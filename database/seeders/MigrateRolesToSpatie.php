<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Roles as LegacyRole;
use App\Models\Admins;
use Spatie\Permission\Models\Role;

class MigrateRolesToSpatie extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Migrar Roles
        $legacyRoles = LegacyRole::all();
        
        foreach ($legacyRoles as $lRole) {
            // Crear o encontrar el rol en Spatie para el guard 'web' incluyendo la descripción
            Role::updateOrCreate(
                ['name' => $lRole->name, 'guard_name' => 'web'],
                ['description' => $lRole->description]
            );
        }

        // 2. Asignar Roles a Admins
        $admins = Admins::all();
        foreach ($admins as $admin) {
            $legacyRole = LegacyRole::find($admin->rol_id);
            if ($legacyRole) {
                // Asignar el rol (Spatie buscará el rol con el mismo nombre y guard compatible)
                $admin->syncRoles([$legacyRole->name]);
            }
        }
    }
}
