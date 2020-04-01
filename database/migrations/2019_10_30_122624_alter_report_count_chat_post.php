<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterReportCountChatPost extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_post', function (Blueprint $table) {
            DB::statement("ALTER TABLE chat_post CHANGE COLUMN report_count report_count INTEGER  NULL DEFAULT 0");
            DB::statement("UPDATE `chat_post` SET `report_count` = 0");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('chat_post');
    }
}
