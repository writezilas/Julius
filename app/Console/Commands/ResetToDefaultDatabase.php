<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class ResetToDefaultDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:reset-to-default 
                            {--force : Force the operation without confirmation}
                            {--structure-only : Only reset structure, skip data}
                            {--data-only : Only reset data, skip structure}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset database to the default migration state (as of 2025-09-26)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $force = $this->option('force');
        $structureOnly = $this->option('structure-only');
        $dataOnly = $this->option('data-only');
        
        if (!$force) {
            $this->warn('🚨 WARNING: This will completely reset your database!');
            $this->line('This operation will:');
            $this->line('• Drop all existing tables');
            $this->line('• Recreate the default database structure');
            if (!$dataOnly) {
                $this->line('• Import default data (users, settings, etc.)');
            }
            $this->line('• Reset migration tracking');
            $this->newLine();
            
            if (!$this->confirm('Are you sure you want to continue?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }
        
        try {
            $this->info('🔄 Starting database reset to default state...');
            $this->newLine();
            
            // Step 1: Drop all tables
            if (!$dataOnly) {
                $this->dropAllTables();
            }
            
            // Step 2: Import structure
            if (!$dataOnly) {
                $this->importDatabaseStructure();
            }
            
            // Step 3: Import data
            if (!$structureOnly) {
                $this->importDefaultData();
            }
            
            // Step 4: Mark all migrations as run
            if (!$dataOnly) {
                $this->markMigrationsAsRun();
            }
            
            $this->newLine();
            $this->info('✅ Database has been successfully reset to default state!');
            $this->line('The database now matches the default migration state as of 2025-09-26.');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to reset database: ' . $e->getMessage());
            $this->line('Error details: ' . $e->getTraceAsString());
            return 1;
        }
    }
    
    /**
     * Drop all tables from the database
     */
    private function dropAllTables()
    {
        $this->info('🗑️  Dropping all existing tables...');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $tableKey = 'Tables_in_' . $databaseName;
        
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            $this->line("   • Dropped table: {$tableName}");
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('   ✅ All tables dropped successfully');
        $this->newLine();
    }
    
    /**
     * Import the default database structure
     */
    private function importDatabaseStructure()
    {
        $this->info('🏗️  Importing default database structure...');
        
        $structureFile = database_path('schema/default_database_structure.sql');
        
        if (!File::exists($structureFile)) {
            throw new \Exception("Structure file not found: {$structureFile}");
        }
        
        $sql = File::get($structureFile);
        
        // Remove the database creation/use statements and execute
        $sql = preg_replace('/^(CREATE DATABASE|USE).*$/m', '', $sql);
        
        // Split by semicolon and execute each statement
        $statements = array_filter(explode(';', $sql), 'trim');
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
                DB::unprepared($statement);
            }
        }
        
        $this->info('   ✅ Database structure imported successfully');
        $this->newLine();
    }
    
    /**
     * Import default data
     */
    private function importDefaultData()
    {
        $this->info('📊 Importing default data...');
        
        $dataFile = database_path('schema/default_database_data.sql');
        
        if (!File::exists($dataFile)) {
            $this->warn('   ⚠️  Data file not found, skipping data import');
            $this->line("   Expected file: {$dataFile}");
            return;
        }
        
        $sql = File::get($dataFile);
        
        // Split by semicolon and execute each statement
        $statements = array_filter(explode(';', $sql), 'trim');
        
        $importedTables = [];
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^\s*--/', $statement)) {
                DB::unprepared($statement);
                
                // Extract table name for logging
                if (preg_match('/INSERT INTO `?([^`\s]+)`?/', $statement, $matches)) {
                    $tableName = $matches[1];
                    if (!in_array($tableName, $importedTables)) {
                        $importedTables[] = $tableName;
                        $this->line("   • Importing data for: {$tableName}");
                    }
                }
            }
        }
        
        $this->info('   ✅ Default data imported successfully');
        $this->newLine();
    }
    
    /**
     * Mark all existing migrations as run
     */
    private function markMigrationsAsRun()
    {
        $this->info('📝 Updating migration tracking...');
        
        // Get all migration files
        $migrationPath = database_path('migrations');
        $migrationFiles = File::files($migrationPath);
        
        // Clear existing migration records
        DB::table('migrations')->truncate();
        
        $batch = 1;
        foreach ($migrationFiles as $file) {
            $migration = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            
            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => $batch
            ]);
            
            $this->line("   • Marked as run: {$migration}");
        }
        
        $this->info('   ✅ Migration tracking updated successfully');
        $this->newLine();
    }
}
