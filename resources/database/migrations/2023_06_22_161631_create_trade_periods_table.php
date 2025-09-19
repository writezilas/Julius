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
        Schema::create('trade_periods', function (Blueprint $table) {
            $table->id();
            $table->integer('days');
            $table->integer('percentage')->default(0);
            $table->tinyInteger('status')->default(1)->comment('1 => Active, 2 => Inactive');
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
        Schema::dropIfExists('trade_periods');
    }
};
