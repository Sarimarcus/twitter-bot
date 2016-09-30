<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAlexandrinesTableUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alexandrines', function (Blueprint $table) {
            $table->string('screen_name')->after('lang');
            $table->string('profile_image_url')->after('screen_name');
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
            $table->dropColumn(['screen_name', 'profile_image_url']);
        });
    }
}
