<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlexandrinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alexandrines', function (Blueprint $table) {
            $table->bigInteger('tweet_id')->unique();
            $table->bigInteger('user_id');
            $table->string('text');
            $table->string('lang');
            $table->boolean('retweeted')->default(0);
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
        Schema::drop('alexandrines');
    }
}
