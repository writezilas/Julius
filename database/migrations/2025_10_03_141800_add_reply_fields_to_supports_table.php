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
        Schema::table('supports', function (Blueprint $table) {
            $table->text('admin_reply')->nullable()->after('message');
            $table->timestamp('replied_at')->nullable()->after('admin_reply');
            $table->unsignedBigInteger('replied_by')->nullable()->after('replied_at');
            $table->boolean('admin_notified')->default(false)->after('replied_by');
            
            // Add foreign key constraint for replied_by (assuming it references users table)
            $table->foreign('replied_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('supports', function (Blueprint $table) {
            $table->dropForeign(['replied_by']);
            $table->dropColumn(['admin_reply', 'replied_at', 'replied_by', 'admin_notified']);
        });
    }
};