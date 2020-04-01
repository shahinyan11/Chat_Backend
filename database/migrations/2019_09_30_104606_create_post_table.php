<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('threadid')->nullable();
            $table->integer('parentid')->nullable();
            $table->string('username');
            $table->integer('userid')->nullable();
            $table->string('title');
            $table->integer('dateline');
            $table->mediumText('pagetext')->nullable();
            $table->smallInteger('allowsmilie')->default(0);
            $table->smallInteger('showsignature')->default(0);
            $table->char('ipaddress',15);
            $table->smallInteger('iconid')->default(0);
            $table->smallInteger('visible')->default(0);
            $table->smallInteger('attach')->default(0);
            $table->smallInteger('infraction')->default(0);
            $table->integer('reportthreadid')->default(0);
            $table->integer('post_thanks_amount')->default(0);

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
        Schema::dropIfExists('post');
    }
}
