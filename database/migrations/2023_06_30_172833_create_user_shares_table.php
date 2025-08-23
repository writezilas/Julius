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
        Schema::create('user_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_no')->unique();
            $table->double('amount', 15,2)->default(0.00);
            $table->double('balance', 15,2)->default(0.00);
            $table->integer('period')->default(0);
            $table->integer('share_will_get')->default(0);
            $table->integer('total_share_count')->default(0);
            $table->enum('status', ['pending', 'pairing', 'paired', 'completed', 'failed'])->default('pending');
            $table->tinyInteger('is_sold')->default(0);
            $table->dateTime('start_date')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_shares');
    }
};
