<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('user_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('torrent_id')->constrained()->onDelete('cascade');
            $table->boolean('free')->default(false); // Free download
            $table->boolean('double')->default(false); // Double upload
            $table->timestamp('expires_at')->nullable(); // Expiration time
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_slots');
    }
};
