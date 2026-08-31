<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTorrentsTable extends Migration
{
    public function up()
    {
        Schema::create('torrents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('genre')->nullable(); // Optional field
            $table->text('mediainfo')->nullable(); // Optional field
            $table->string('info_hash')->unique();
            $table->string('file_name')->nullable(); // Optional field
            $table->bigInteger('size')->unsigned()->nullable(); // Optional field
            $table->integer('num_files')->unsigned()->nullable(); // Optional field
            $table->integer('seeders')->unsigned()->default(0);
            $table->integer('leechers')->unsigned()->default(0);
            $table->string('announce')->nullable(); // Optional field
            $table->foreignId('owner')->constrained('users')->onDelete('cascade');
            $table->string('imdbid')->nullable(); // Optional field
            $table->string('tmdbid')->nullable(); // Optional field
            $table->string('steamid')->nullable(); // Optional field
            $table->boolean('approved')->default(true);
            $table->boolean('free')->default(false);
            $table->boolean('seedbox')->default(false);
            $table->boolean('double')->default(false);
            $table->boolean('sticky')->default(false);
            $table->boolean('recommended')->default(false);
            $table->boolean('anon')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('torrents');
    }
}
