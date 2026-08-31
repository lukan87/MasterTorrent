<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTorrentIdToCommentsTable extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            // Add the torrent_id column
            $table->unsignedBigInteger('torrent_id')->nullable()->after('id'); // Adjust placement if necessary
        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            // Drop the torrent_id column if rolling back
            $table->dropColumn('torrent_id');
        });
    }
}
