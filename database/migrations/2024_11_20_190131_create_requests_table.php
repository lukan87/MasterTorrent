<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('category_id');
            $table->string('imdb_url')->nullable();
            $table->string('tmdb_url')->nullable();
            $table->string('steam_url')->nullable();
            $table->string('image')->nullable(); // Path to the image
            $table->text('description')->nullable(); // Description for the torrent request
            $table->enum('filled', ['yes', 'no'])->default('no'); // Column to track if the request is filled
            $table->timestamps();

            // Foreign key to categories table
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('requests');
    }
}

