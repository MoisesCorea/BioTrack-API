<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Admins
            'view_admins',
            'manage_admins',
            
            // Roles
            'view_roles',
            'manage_roles',
            
            // Departments
            'view_departments',
            'manage_departments',
            
            // Shifts
            'view_shifts',
            'manage_shifts',
            
            // Events
            'view_events',
            'manage_events',
            
            // Users
            'view_users',
            'manage_users',
            
            // Reports
            'view_reports',
            
            // Justifications
            'view_justifications',
            'manage_justifications',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Roles exist from RolesSeeder
        $roleAdmin = Role::findByName('Admin', 'web');
        $roleAdmin1 = Role::findByName('Admin-1', 'web');
        $roleAdmin2 = Role::findByName('Admin-2', 'web');

        // Admin: everything
        $roleAdmin->givePermissionTo(Permission::all());

        // Admin-1: limited management
        $roleAdmin1->givePermissionTo([
            'view_admins',
            'view_roles',
            'view_departments',
            'manage_departments',
            'view_shifts',
            'manage_shifts',
            'view_events',
            'manage_events',
            'view_users',
            'manage_users',
            'view_reports',
            'view_justifications',
            'manage_justifications',
        ]);

        // Admin-2: view only + restricted management
        $roleAdmin2->givePermissionTo([
            'view_departments',
            'view_events',
            'view_users',
            'view_reports',
        ]);
    }
}
