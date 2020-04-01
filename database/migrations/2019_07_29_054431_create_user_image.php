<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_image', function (Blueprint $table) {
            $table->Increments('id');
            $table->integer('owner_id');
            $table->enum('property_type', ['messenger', 'chat', 'contest', 'contest_entry', 'forum_post', 'profile']);
            $table->integer('property_id');
            $table->enum('privacy', ['public', 'member', 'friends', 'hidden', 'deleted']);
            $table->integer('width');
            $table->integer('height');
            $table->text('src');
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
        Schema::dropIfExists('user_image');
    }
}
