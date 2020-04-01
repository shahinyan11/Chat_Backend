<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEnumFieldToChatPost extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_post', function (Blueprint $table) {
            $table->enum('type', ['user_post','room_join', 'room_left'])->default('user_post')->after('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_post', function (Blueprint $table) {
            //
        });
    }
}
