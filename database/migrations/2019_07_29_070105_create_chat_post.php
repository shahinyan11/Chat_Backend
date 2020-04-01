<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatPost extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_post', function (Blueprint $table) {
            $table->Increments('id');
            $table->integer('chat_room_id')->unsigned();
            $table->foreign('chat_room_id')->references('id')->on('chat_room')->onDelete('cascade');
            $table->integer('owner_id');
            $table->text('body')->nullable();
            $table->integer('attachment_id')->unsigned()->nullable();
            $table->integer('report_count')->nullable();
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
        Schema::dropIfExists('chat_post');
    }
}
