<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBumpedToTorrentsTable extends Migration
{
    public function up()
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->boolean('bumped')->default(false); // Adds bumped column with default value false
        });
    }

    public function down()
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->dropColumn('bumped');
        });
    }
}

