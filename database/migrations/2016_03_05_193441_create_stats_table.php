<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats', function (Blueprint $table) {
            $table->date('date');
            $table->bigInteger('bot_id');
            $table->integer('statuses_count');
            $table->integer('favourites_count');
            $table->integer('followers_count');
            $table->integer('friends_count');
            $table->primary(['date', 'bot_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('stats');
    }
}
