<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('name'); // Movie name
            $table->string('tmdb_id')->unique(); // TMDb ID
            $table->string('imdb_id')->unique(); // IMDb ID
            $table->string('poster_path'); // Poster path
            $table->unsignedBigInteger('collection_id')->nullable(); // Collection ID (if applicable)
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movies');
    }
}
