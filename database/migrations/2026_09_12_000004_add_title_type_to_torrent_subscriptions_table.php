<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the resolved TMDB title and media type to a subscription so the UI
     * (e.g. the profile "Subscribed Torrents" list) can display the clean TMDB
     * name instead of the raw uploaded torrent filename. The type lets us look
     * up the right poster (movie vs tv).
     */
    public function up(): void
    {
        Schema::table('torrent_subscriptions', function (Blueprint $table) {
            $table->string('title')->nullable()->after('source_torrent_id');
            $table->string('type')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('torrent_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['title', 'type']);
        });
    }
};