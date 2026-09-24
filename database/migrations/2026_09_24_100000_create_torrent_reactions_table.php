<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('torrent_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('torrent_id')->constrained('torrents')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('reaction');
            $table->timestamps();
            $table->unique(['torrent_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('torrent_reactions');
    }
};
