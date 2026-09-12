<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A subscription registers a user's interest in a title identified by its
     * IMDb and/or TMDB id. When a new torrent with a matching imdbid or tmdbid
     * is uploaded, the subscriber receives a private message.
     */
    public function up(): void
    {
        Schema::create('torrent_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('imdbid')->nullable();
            $table->string('tmdbid')->nullable();
            $table->unsignedBigInteger('source_torrent_id')->nullable();

            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            // A user may subscribe to a given title only once.
            $table->unique(['user_id', 'imdbid', 'tmdbid'], 'torrent_subscriptions_user_title_unique');

            $table->index('imdbid');
            $table->index('tmdbid');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('torrent_subscriptions');
    }
};