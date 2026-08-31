<?php

// In the migration file
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimesCompletedToTorrentsTable extends Migration
{
    public function up()
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->unsignedBigInteger('times_completed')->default(0)->after('leechers'); // Adjust position as needed
        });
    }

    public function down()
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->dropColumn('times_completed');
        });
    }
}

