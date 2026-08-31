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
        Schema::create('torrent_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('torrent_id')->constrained('torrents')->onDelete('cascade');
            $table->string('path'); // Path to the image file
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('torrent_images');
    }
};
