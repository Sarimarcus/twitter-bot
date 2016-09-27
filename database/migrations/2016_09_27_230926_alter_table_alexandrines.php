<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAlexandrines extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alexandrines', function (Blueprint $table) {
            $table->string('phoneme')->after('rank')->nullable();;
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
            $table->dropColumn('phoneme');
        });
    }
}
