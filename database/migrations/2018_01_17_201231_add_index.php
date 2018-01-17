<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alexandrines', function(Blueprint $table)
        {
            $table->index('phoneme');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alexandrines', function (Blueprint $table)
        {
            $table->dropIndex(['phoneme']);
        });
    }
}
