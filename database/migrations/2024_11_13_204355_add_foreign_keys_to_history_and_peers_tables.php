<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToHistoryAndPeersTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->unsignedBigInteger('torrent_id')->change();
            $table->unsignedBigInteger('user_id')->change();
            $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('peers', function (Blueprint $table) {
            $table->unsignedBigInteger('torrent_id')->change();
            $table->unsignedBigInteger('user_id')->change();
            $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->dropForeign(['torrent_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('peers', function (Blueprint $table) {
            $table->dropForeign(['torrent_id']);
            $table->dropForeign(['user_id']);
        });
    }
}
