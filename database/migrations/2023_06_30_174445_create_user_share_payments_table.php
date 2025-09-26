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
        Schema::create('user_share_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_share_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_share_pair_id')->constrained('user_share_pairs')->cascadeOnDelete();
            $table->unsignedBigInteger('receiver_id');
            $table->unsignedBigInteger('sender_id');
            $table->double('amount', 15,2)->default(0.00);
            $table->string('name')->nullable();
            $table->string('number');
            $table->string('received_phone_no');
            $table->string('txs_id')->nullable();
            $table->text('file')->nullable();
            $table->enum('status', ['pending', 'paid', 'conformed', 'failed'])->default('pending');
            $table->text('note_by_sender')->nullable();
            $table->text('note_by_receiver')->nullable();
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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('user_share_payments');
        Schema::enableForeignKeyConstraints();
    }
};
