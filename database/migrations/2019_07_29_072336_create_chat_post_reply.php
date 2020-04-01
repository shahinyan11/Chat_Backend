<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatPostReply extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_post_reply', function (Blueprint $table) {
            $table->Increments('id');
            $table->integer('chat_post_id')->unsigned();
            $table->foreign('chat_post_id')->references('id')->on('chat_post')->onDelete('cascade');
            $table->integer('owner_id');
            $table->text('body');
            $table->integer('report_count');
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
        Schema::dropIfExists('chat_post_reply');
    }
}
