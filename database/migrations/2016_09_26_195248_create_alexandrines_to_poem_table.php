<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlexandrinesToPoemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alexandrines_to_poem', function (Blueprint $table) {
            $table->bigInteger('alexandrine_id');
            $table->bigInteger('poem_id');
            $table->integer('rank');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('alexandrines_to_poem');
    }
}
