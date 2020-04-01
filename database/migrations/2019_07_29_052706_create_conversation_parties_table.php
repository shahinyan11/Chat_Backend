<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversationPartiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conversation_parties', function (Blueprint $table) {
            $table->Increments('id');
            $table->integer('conversation_id')->unsigned();
            $table->foreign('conversation_id')->references('id')->on('conversation')->onDelete('cascade');
            $table->string('parties', 1000);
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
        Schema::dropIfExists('conversation_parties');
    }
}
