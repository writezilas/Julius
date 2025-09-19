<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('suspension_reason', ['manual', 'automatic', 'payment_failure'])
                  ->nullable()
                  ->default('manual')
                  ->after('suspension_until')
                  ->comment('Reason for suspension: manual (by admin), automatic (system), payment_failure (payment issues)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('suspension_reason');
        });
    }
};
