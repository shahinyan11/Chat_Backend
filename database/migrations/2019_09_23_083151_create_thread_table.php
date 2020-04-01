<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThreadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('thread', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('firstpostid');
            $table->integer('lastpostid');
            $table->integer('lastpost');
            $table->unsignedBigInteger('forum_id');
            $table->foreign('forum_id')->references('id')->on('forum')->onDelete('cascade');
            $table->integer('pollid');
            $table->smallInteger('open');
            $table->integer('replycount')->default(0);
            $table->integer('postercount')->default(0);
            $table->integer('hiddencount')->default(0);
            $table->integer('deletedcount')->default(0);
            $table->string('postusername');
            $table->integer('postuserid');
            $table->string('lastposter');
            $table->integer('lastposterid');
            $table->integer('dateline');
            $table->integer('views');
            $table->integer('iconid');
            $table->smallInteger('visible');
            $table->smallInteger('sticky');
            $table->smallInteger('votenum');
            $table->smallInteger('votetotal');
            $table->smallInteger('attach');
            $table->string('similar',55);
            $table->text('taglist')->nullable();
            $table->text('keywords')->nullable();
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
        Schema::dropIfExists('thread');
    }
}
