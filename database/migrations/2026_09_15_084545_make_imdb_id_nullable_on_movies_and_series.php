<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Some TMDB movies/series genuinely have no IMDb ID (e.g. obscure
        // direct-to-video and indie titles return a null external_ids.imdb_id).
        // Allow NULL so those titles can still be added to the library.
        Schema::table('movies', function (Blueprint $table) {
            $table->string('imdb_id')->nullable()->change();
        });

        Schema::table('series', function (Blueprint $table) {
            $table->string('imdb_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('imdb_id')->nullable(false)->change();
        });

        Schema::table('series', function (Blueprint $table) {
            $table->string('imdb_id')->nullable(false)->change();
        });
    }
};
