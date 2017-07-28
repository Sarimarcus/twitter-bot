<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAlexandrinesTableAgain extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alexandrines', function (Blueprint $table) {
            $table->string('last_word')->after('phoneme')->nullable();
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
            $table->dropColumn(['last_word']);
        });
    }
}
