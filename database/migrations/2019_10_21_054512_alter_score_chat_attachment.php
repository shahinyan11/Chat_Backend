<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScoreChatAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_attachment', function (Blueprint $table) {
            DB::statement("ALTER TABLE chat_attachment CHANGE COLUMN score score INTEGER NOT NULL DEFAULT 0");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_attachment');
    }
}
