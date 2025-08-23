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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_share_id')->constrained('user_shares')->cascadeOnDelete();
            $table->foreignId('seller_share_id')->constrained('user_shares')->cascadeOnDelete();
            $table->foreignId('user_share_pair_id')->constrained('user_share_pairs')->cascadeOnDelete();
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            // Ensure unique conversation per share pair
            $table->unique('user_share_pair_id');
            
            // Index for better performance
            $table->index(['buyer_share_id', 'seller_share_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conversations');
    }
};
