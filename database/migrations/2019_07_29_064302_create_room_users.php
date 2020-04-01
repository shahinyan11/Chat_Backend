<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoomUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('room_users', function (Blueprint $table) {
            $table->Increments('id');
            $table->integer('user_id');
            $table->integer('chat_room_id')->unsigned();
            $table->foreign('chat_room_id')->references('id')->on('chat_room')->onDelete('cascade');
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
        Schema::dropIfExists('chat_users');
    }
}
