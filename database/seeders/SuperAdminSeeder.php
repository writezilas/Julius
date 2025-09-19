<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // First, sync all permissions from the JSON file
        $this->syncPermissions();
        
        // Create or update Super Admin role
        $superAdminRole = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);
        
        // Assign all permissions to Super Admin role
        $allPermissions = Permission::all();
        $superAdminRole->syncPermissions($allPermissions);
        
        // Create or update superadmin user
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@autobidder.com'],
            [
                'name' => 'Auto Bidder',
                'username' => 'superadmin',
                'phone' => '03400000000',
                'role_id' => 1,
                'email' => 'admin@autobidder.com',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
                'avatar' => 'assets/images/users/avatar-1.jpg',
            ]
        );
        
        // Assign Super Admin role to the user
        $superAdmin->assignRole($superAdminRole);
        
        echo "✅ Super Admin user created successfully!\n";
        echo "📧 Email: admin@autobidder.com\n";
        echo "🔑 Password: 123456\n";
        echo "👤 Username: superadmin\n";
        echo "🎭 Role: Super Admin\n";
        echo "🔐 Permissions: " . $allPermissions->count() . " permissions assigned\n";
    }
    
    /**
     * Sync permissions from JSON file
     */
    private function syncPermissions()
    {
        $jsonPath = database_path('seeders/data/permissions.json');
        
        if (!file_exists($jsonPath)) {
            throw new \Exception("Permissions file not found at: {$jsonPath}");
        }
        
        $permissions = json_decode(file_get_contents($jsonPath), true);
        
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
        
        echo "📝 Synced " . count($permissions) . " permissions\n";
    }
}
