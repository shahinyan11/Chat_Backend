<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_attachment', function (Blueprint $table) {
            $table->Increments('id');
            $table->enum('type', ['image', 'video']);
            $table->integer('src_id')->unsigned();
            $table->foreign('src_id')->references('id')->on('user_image');
            $table->integer('chat_post_id')->unsigned();
            $table->foreign('chat_post_id')->references('id')->on('chat_post')->onDelete('cascade');
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
        Schema::dropIfExists('chat_attachment');
    }
}
