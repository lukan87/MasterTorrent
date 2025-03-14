<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->unique(['user_id', 'torrent_id'], 'user_torrent_unique');
        });
    }

    public function down()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->dropUnique('user_torrent_unique');
        });
    }
};
