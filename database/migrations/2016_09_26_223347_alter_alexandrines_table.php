<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAlexandrinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alexandrines', function (Blueprint $table) {
            $table->bigInteger('poem_id')->after('lang')->nullable();
            $table->integer('rank')->after('poem_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alexandrines', function (Blueprint $table) {
            $table->dropColumn(['poem_id', 'rank']);
        });
    }
}
