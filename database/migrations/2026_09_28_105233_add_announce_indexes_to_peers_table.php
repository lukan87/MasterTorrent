<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peers', function (Blueprint $table) {
            // One user may use multiple clients on the same torrent.
            // Each peer_id represents a separate client/session.
            $table->unique(
                ['torrent_id', 'user_id', 'peer_id'],
                'peers_torrent_user_peer_unique'
            );

            // Optimises the peer list returned during announces.
            $table->index(
                ['torrent_id', 'client_updated_at'],
                'peers_torrent_updated_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('peers', function (Blueprint $table) {
            $table->dropUnique('peers_torrent_user_peer_unique');
            $table->dropIndex('peers_torrent_updated_idx');
        });
    }
};