<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use DB;
class AlterEnumReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('reports', function (Blueprint $table) {
            DB::statement("ALTER TABLE reports CHANGE COLUMN property_type property_type ENUM('image', 'video', 'chat_post', 'chat_post_comment', 'chat_attachment', 'thread', 'thread_post', 'thread_post_comment', 'profile')");
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
