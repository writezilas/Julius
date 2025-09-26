<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_shares', function (Blueprint $table) {
            $table->timestamp('floated_to_market_at')->nullable()->after('selling_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_shares', function (Blueprint $table) {
            $table->dropColumn('floated_to_market_at');
        });
    }
};