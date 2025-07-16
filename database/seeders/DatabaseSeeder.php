<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view_any_user',
            'view_user',
            'create_user',
            'update_user',
            'delete_user',
            'delete_any_user',
            'view_any_role',
            'view_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create super admin role
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

        // Give all permissions to super admin
        $superAdminRole->givePermissionTo(Permission::all());

        // Create admin role with user and role management permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user', 'delete_any_user',
            'view_any_role', 'view_role', 'create_role', 'update_role', 'delete_role', 'delete_any_role'
        ]);

        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Create test user with admin role
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );
        $testUser->assignRole('admin');
    }
}
