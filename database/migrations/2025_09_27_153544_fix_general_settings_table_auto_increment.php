<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix the id column to have auto-increment if it doesn't already
        DB::statement('ALTER TABLE general_settings MODIFY id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration cannot be easily reversed since removing auto-increment 
        // could cause issues with existing data. Leave empty.
    }
};
