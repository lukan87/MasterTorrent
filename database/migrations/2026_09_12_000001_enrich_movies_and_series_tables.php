<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enrich the movies & series tables with TMDB metadata, ratings and a
     * popularity/view counter so the detail & listing pages can look great
     * without round-tripping to the TMDB API on every view.
     */
    public function up(): void
    {
        // ----- MOVIES (TMDB movie endpoint) -----
        Schema::table('movies', function (Blueprint $table) {
            $table->date('release_date')->nullable()->after('name');
            $table->unsignedInteger('runtime')->nullable()->after('release_date');
            $table->decimal('vote_average', 4, 1)->nullable()->after('runtime');
            $table->unsignedInteger('vote_count')->default(0)->after('vote_average');
            $table->string('tagline')->nullable()->after('vote_count');
            $table->string('status')->nullable()->after('tagline');
            $table->json('genres')->nullable()->after('status');
            $table->unsignedBigInteger('views')->default(0)->after('genres');
        });

        // ----- SERIES (TMDB tv endpoint) -----
        Schema::table('series', function (Blueprint $table) {
            $table->date('first_air_date')->nullable()->after('name');
            $table->unsignedInteger('number_of_seasons')->nullable()->after('first_air_date');
            $table->unsignedInteger('number_of_episodes')->nullable()->after('number_of_seasons');
            $table->decimal('vote_average', 4, 1)->nullable()->after('number_of_episodes');
            $table->unsignedInteger('vote_count')->default(0)->after('vote_average');
            $table->string('tagline')->nullable()->after('vote_count');
            $table->string('status')->nullable()->after('tagline');
            $table->json('genres')->nullable()->after('status');
            $table->unsignedBigInteger('views')->default(0)->after('genres');
            $table->string('trailer_key')->nullable()->after('views');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn([
                'release_date', 'runtime', 'vote_average', 'vote_count',
                'tagline', 'status', 'genres', 'views',
            ]);
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn([
                'first_air_date', 'number_of_seasons', 'number_of_episodes',
                'vote_average', 'vote_count', 'tagline', 'status',
                'genres', 'views', 'trailer_key',
            ]);
        });
    }
};