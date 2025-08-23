<?php

use App\Models\Policy;
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
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('heading_one')->nullable();
            $table->longText('content_one')->nullable();
            $table->string('heading_two')->nullable();
            $table->longText('content_two')->nullable();
            $table->timestamps();
        });

        Policy::create([
            'title'         => 'How it works',
            'slug'          => 'how-it-work',
            'heading_one'   => 'How To Bid (Buy Shares)',
            'content_one'   => "",
            'heading_two'   => 'How Do We Benefit From All These??',
            'content_two'   => 'How Do We Benefit From All These??',
            'created_at'    => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('policies');
    }
};
