<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('torrent_id')->constrained()->onDelete('cascade'); // Foreign key referencing torrents table
            $table->string('filename'); // Column for the filename
            $table->bigInteger('size'); // Column for the size in bytes
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('files');
    }
};
