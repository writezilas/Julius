<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the JSON file
        $jsonPath = database_path('seeders/data/permissions.json');
        
        // Decode to array
        $permissions = collect(json_decode(file_get_contents($jsonPath), true));

        // Set max progress
        $this->command->getOutput()->progressStart($permissions->count());

        // Update or Insert
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert([
                'name' => $permission['name']
            ], $permission);
        }

        // Finish progress
        $this->command->getOutput()->progressFinish();
    }
}
