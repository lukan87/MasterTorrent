<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torrents', function (Blueprint $table) {

            /*
             * Main browse:
             *
             * WHERE seeders > 0
             * ORDER BY sticky DESC, created_at DESC, id DESC
             */
            $table->index(
                ['seeders', 'sticky', 'created_at', 'id'],
                'torrents_browse_active_idx'
            );

            /*
             * Category browsing:
             *
             * WHERE category_id = ?
             * AND seeders > 0
             */
            $table->index(
                ['category_id', 'seeders', 'created_at'],
                'torrents_category_active_idx'
            );

            /*
             * TMDB filtering.
             */
            $table->index(
                'tmdbid',
                'torrents_tmdbid_idx'
            );

            /*
             * Common chronological ordering.
             */
            $table->index(
                'created_at',
                'torrents_created_at_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->dropIndex('torrents_browse_active_idx');
            $table->dropIndex('torrents_category_active_idx');
            $table->dropIndex('torrents_tmdbid_idx');
            $table->dropIndex('torrents_created_at_idx');
        });
    }
};