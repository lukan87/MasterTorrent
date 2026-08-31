<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('torrent_logs', function (Blueprint $table) {
            $table->id();

            // Who performed the action
            $table->unsignedBigInteger('user_id');

            // Torrent affected
            $table->unsignedBigInteger('torrent_id')->nullable();

            // Action type: uploaded, edited, deleted
            $table->string('action');

            // Optional: store extra details (e.g. changed fields)
            $table->text('description')->nullable();

            $table->timestamps();

            // Foreign keys (optional but recommended)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('torrent_logs');
    }
};
