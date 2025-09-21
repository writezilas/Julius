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
        Schema::table('user_share_pairs', function (Blueprint $table) {
            // Change paired_user_share_id to match user_shares.id type (bigint unsigned)
            $table->unsignedBigInteger('paired_user_share_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_share_pairs', function (Blueprint $table) {
            // Revert back to int
            $table->integer('paired_user_share_id')->change();
        });
    }
};
