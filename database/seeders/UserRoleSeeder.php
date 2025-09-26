<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create User role for regular users
        $userRole = Role::firstOrCreate([
            'name' => 'User',
            'guard_name' => 'web'
        ]);
        
        // Define permissions for regular users (limited permissions)
        $userPermissions = [
            // Users can view their own profile and data
            'view-analytic' => false, // No analytics access
            'view-share-pending-confirmation' => false, // No admin confirmation access
        ];
        
        // For now, regular users don't get any admin permissions
        // You can add specific user permissions here as needed
        
        echo "✅ User role created successfully!\n";
        echo "🎭 Role: User (for regular users with role_id = 2)\n";
        echo "🔐 Permissions: No admin permissions assigned (user panel access only)\n";
    }
}
