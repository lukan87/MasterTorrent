<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('torrent_thanks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('torrent_id')->constrained('torrents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['torrent_id', 'user_id']); // To prevent duplicate thanks from the same user to the same torrent
        });
    }

    public function down()
    {
        Schema::dropIfExists('torrent_thanks');
    }
};
