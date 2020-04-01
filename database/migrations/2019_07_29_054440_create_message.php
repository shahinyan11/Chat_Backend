<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message', function (Blueprint $table) {
            $table->Increments('id');
            $table->integer('author_id');
            $table->text('body')->nullable();
            $table->integer('conversation_id')->unsigned();
            $table->foreign('conversation_id')->references('id')->on('conversation')->onDelete('cascade');;
            $table->integer('call_id')->unsigned()->nullable();
            $table->foreign('call_id')->references('id')->on('call');
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
        Schema::dropIfExists('message');
    }
}
