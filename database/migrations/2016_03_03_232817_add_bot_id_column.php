<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBotIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->bigInteger('bot_id')->after('id');
        });

        Schema::table('tweets', function ($table) {
            $table->bigInteger('bot_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function ($table) {
            $table->dropColumn('bot_id');
        });

        Schema::table('tweets', function ($table) {
            $table->dropColumn('bot_id');
        });
    }
}
