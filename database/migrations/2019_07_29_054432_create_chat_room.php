<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChatRoom extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_room', function (Blueprint $table) {
            $table->Increments('id');
            $table->text('name');
            $table->integer('owner_id');
            $table->enum('privacy', ['public','country','user','user-protected','private']);
            $table->text('retention')->nullable();
            $table->text('description')->nullable();
            $table->text('password')->nullable();
            $table->text('setting')->nullable();
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
        Schema::dropIfExists('chat_room');
    }
}
