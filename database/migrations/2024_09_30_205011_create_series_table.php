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
        Schema::create('series', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Movie name
            $table->string('tmdb_id')->unique(); // TMDb ID
            $table->string('imdb_id')->unique(); // IMDb ID
            $table->string('poster_path'); // Poster path
            $table->timestamps(); // Created and updated timesta
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('series');
    }
};
