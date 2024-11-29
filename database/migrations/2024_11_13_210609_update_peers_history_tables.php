<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePeersHistoryTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Updating the 'peers' table
        Schema::table('peers', function (Blueprint $table) {
            if (!Schema::hasColumn('peers', 'torrent_id')) {
                $table->bigInteger('torrent_id')->unsigned()->nullable()->after('id')->index();
                $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('cascade');
            }

            if (!Schema::hasColumn('peers', 'user_id')) {
                $table->integer('user_id')->nullable()->after('torrent_id')->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });

        // Updating the 'history' table
        Schema::table('history', function (Blueprint $table) {
            if (!Schema::hasColumn('history', 'torrent_id')) {
                $table->bigInteger('torrent_id')->unsigned()->nullable()->after('user_id')->index();
                $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropping the added columns and foreign keys from the 'peers' table
        Schema::table('peers', function (Blueprint $table) {
            if (Schema::hasColumn('peers', 'torrent_id')) {
                $table->dropForeign(['torrent_id']);
                $table->dropColumn('torrent_id');
            }

            if (Schema::hasColumn('peers', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        // Dropping the added column and foreign key from the 'history' table
        Schema::table('history', function (Blueprint $table) {
            if (Schema::hasColumn('history', 'torrent_id')) {
                $table->dropForeign(['torrent_id']);
                $table->dropColumn('torrent_id');
            }
        });
    }
}
