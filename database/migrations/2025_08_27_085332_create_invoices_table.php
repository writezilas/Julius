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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // User who created the invoice
            $table->unsignedBigInteger('share_id'); // Reference to user_shares table
            $table->unsignedBigInteger('reff_user_id')->nullable(); // Referral user id
            $table->decimal('old_amount', 15, 2)->default(0); // Original amount
            $table->decimal('add_amount', 15, 2)->default(0); // Additional amount
            $table->decimal('new_amount', 15, 2)->default(0); // Total amount
            $table->string('status')->default('pending'); // Invoice status
            $table->text('description')->nullable(); // Invoice description
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('share_id')->references('id')->on('user_shares')->onDelete('cascade');
            $table->foreign('reff_user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for better performance
            $table->index('user_id');
            $table->index('share_id');
            $table->index('reff_user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};
