<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCall extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call', function (Blueprint $table) {
            $table->Increments('id');
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->string('duration')->nullable();
            $table->enum('dispositions', ['rejected', 'no_answer', 'answered']);
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
        Schema::dropIfExists('call');
    }
}
