<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('reported_by');
            $table->enum('property_type', ['image', 'video', 'chat_post', 'chat_post_comment', 'chat_attachment', 'thread', 'thread_post', 'thread_post_comment', 'profile']);
            $table->integer('property_id');
            $table->enum('action_taken', ['ignored', 'deleted', 'deactivated', 'other'])->nullable();
            $table->text('notes')->nullable();
            $table->integer('action_by')->nullable();
            $table->text('action_date')->nullable();
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
        Schema::dropIfExists('reports');
    }
}
