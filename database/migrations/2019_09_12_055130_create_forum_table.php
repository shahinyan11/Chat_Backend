<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForumTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('title_clean');
            $table->text('description')->nullable();
            $table->text('description_clean')->nullable();
            $table->integer('options')->default(0);
            $table->smallInteger('order')->default(0);
            $table->string('password')->nullable();
            $table->tinyInteger('private')->default(0);
            $table->integer('replycount')->default(0);
            $table->smallInteger('daysprune')->default(0);
            $table->smallInteger('parentid')->default(0);
            $table->string('parentlist');
            $table->string('childlist')->nullable();
            $table->mediumInteger('threadcount')->default(0);
            $table->text('lastthread');
            $table->integer('lastthreadid')->nullable();
            $table->integer('lastpost')->nullable();
            $table->string('lastposter');
            $table->integer('lastposterid')->nullable();
            $table->integer('lastpostid')->nullable();
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
        Schema::dropIfExists('forum');
    }
}
