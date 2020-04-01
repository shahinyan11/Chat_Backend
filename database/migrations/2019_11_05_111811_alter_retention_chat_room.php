<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterRetentionChatRoom extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_room', function (Blueprint $table) {
            DB::statement("ALTER TABLE chat_room CHANGE COLUMN retention retention INTEGER  NULL DEFAULT 0");
            DB::statement("UPDATE `chat_room` SET `retention` = 0");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_room');
    }
}
