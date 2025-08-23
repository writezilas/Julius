<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ChatSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $this->insertDefaultSettings();
    }

    /**
     * Insert default chat settings
     */
    private function insertDefaultSettings()
    {
        $defaultSettings = [
            [
                'key' => 'chat_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable or disable the chat system'
            ],
            [
                'key' => 'chat_character_limit',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Maximum character limit for chat messages'
            ],
            [
                'key' => 'chat_file_upload_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Allow file uploads in chat'
            ],
            [
                'key' => 'chat_max_file_size',
                'value' => '5120',
                'type' => 'integer',
                'description' => 'Maximum file size in KB for chat uploads'
            ]
        ];

        foreach ($defaultSettings as $setting) {
            \DB::table('chat_settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_settings');
    }
};
