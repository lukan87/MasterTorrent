<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subtitles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('torrent_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('language', 10)->nullable(); // en, ro, fr, etc
            $table->string('original_name');
            $table->string('file_path');
            $table->string('extension', 10);
            $table->unsignedBigInteger('size');

            $table->foreignId('uploaded_by')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtitles');
    }
};

