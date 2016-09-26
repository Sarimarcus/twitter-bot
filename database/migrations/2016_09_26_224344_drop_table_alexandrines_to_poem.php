<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropTableAlexandrinesToPoem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('alexandrines_to_poem');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('alexandrines_to_poem', function (Blueprint $table) {
            $table->bigInteger('alexandrine_id');
            $table->bigInteger('poem_id');
            $table->integer('rank');
        });
    }
}
