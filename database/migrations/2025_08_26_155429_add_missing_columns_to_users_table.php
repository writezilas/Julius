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
            // Add missing columns from the original users table structure
            $table->enum('business_account_id', ['1', '2'])->default('1')->comment('1=mpesa,2=till')->after('avatar');
            $table->longText('business_profile')->nullable()->after('business_account_id');
            $table->integer('trade_id')->nullable()->after('business_profile');
            $table->enum('status', ['pending', 'block', 'suspend', 'fine'])->default('pending')->after('balance');
            $table->integer('ref_amount')->default(0)->after('status');
            $table->string('mode', 100)->default('light')->after('ref_amount');
            $table->timestamp('block_until')->nullable()->after('mode');
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
            // Drop the added columns in reverse order
            $table->dropColumn([
                'block_until',
                'mode',
                'ref_amount',
                'status',
                'trade_id',
                'business_profile',
                'business_account_id'
            ]);
        });
    }
};
