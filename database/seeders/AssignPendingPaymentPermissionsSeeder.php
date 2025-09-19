<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AssignPendingPaymentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the Super Admin role (ID = 2)
        $superAdminRole = Role::find(2);
        
        if (!$superAdminRole) {
            $this->command->error('Super Admin role (ID: 2) not found!');
            return;
        }

        // List of new pending payment confirmation permissions
        $newPermissions = [
            'pending-payment-confirmation-index',
            'pending-payment-confirmation-view',
            'pending-payment-confirmation-approve',
            'pending-payment-confirmation-decline'
        ];

        $this->command->info("Assigning new permissions to {$superAdminRole->name} role...");

        foreach ($newPermissions as $permissionName) {
            // Find the permission
            $permission = Permission::where('name', $permissionName)->first();
            
            if ($permission) {
                // Check if role already has this permission
                if (!$superAdminRole->hasPermissionTo($permission)) {
                    $superAdminRole->givePermissionTo($permission);
                    $this->command->info("✓ Assigned permission: {$permissionName}");
                } else {
                    $this->command->info("- Permission already assigned: {$permissionName}");
                }
            } else {
                $this->command->warn("✗ Permission not found: {$permissionName}");
            }
        }

        $this->command->info('Permission assignment completed!');
    }
}
