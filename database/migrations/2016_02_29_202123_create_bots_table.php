<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->bigInteger('id')->unique();
            $table->string('screen_name');
            $table->string('name');
            $table->string('twitter_consumer_key');
            $table->string('twitter_consumer_secret');
            $table->string('twitter_access_token');
            $table->string('twitter_access_token_secret');
            $table->string('lang');
            $table->integer('statuses_count');
            $table->integer('favourites_count');
            $table->integer('followers_count');
            $table->integer('friends_count');
            $table->text('configuration');
            $table->boolean('online')->default(0);
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
        Schema::drop('bots');
    }
}
