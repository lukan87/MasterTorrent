<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeedbonusColumnsToPeersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('peers', function (Blueprint $table) {
            $table->timestamp('started')->nullable()->after('user_id'); // Adjust 'some_column' to be your desired reference point
            $table->timestamp('last_awarded')->nullable()->after('started'); // Optional, if you want to set the order of the columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('peers', function (Blueprint $table) {
            $table->dropColumn(['started', 'last_awarded']);
        });
    }
}

