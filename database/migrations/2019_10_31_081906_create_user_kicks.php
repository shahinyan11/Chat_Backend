<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserKicks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_kicks', function (Blueprint $table) {
            $table->Increments('id');
            $table->integer('user_id');
            $table->integer('chat_room_id')->unsigned();
            $table->foreign('chat_room_id')->references('id')->on('chat_room')->onDelete('cascade');
            $table->integer('kick_count')->default(0);
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
        Schema::dropIfExists('user_kicks');
    }
}
