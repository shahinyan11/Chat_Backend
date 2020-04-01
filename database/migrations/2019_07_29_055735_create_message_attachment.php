<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_attachment', function (Blueprint $table) {
            $table->Increments('id');
            $table->enum('type', ['image', 'video']);
            $table->integer('src_id')->unsigned();
            $table->foreign('src_id')->references('id')->on('user_image');
            $table->integer('message_id')->unsigned();
            $table->foreign('message_id')->references('id')->on('message')->onDelete('cascade');
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
        Schema::dropIfExists('message_attachment');
    }
}
