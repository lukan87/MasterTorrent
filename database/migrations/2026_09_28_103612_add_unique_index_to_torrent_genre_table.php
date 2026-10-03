<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torrent_genre', function (Blueprint $table) {
            $table->unique(
                ['torrent_id', 'genre_id'],
                'torrent_genre_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('torrent_genre', function (Blueprint $table) {
            $table->dropUnique('torrent_genre_unique');
        });
    }
};