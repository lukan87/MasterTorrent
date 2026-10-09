<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seedbox_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seedbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('torrent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_hash', 40);
            $table->string('name')->nullable();
            $table->longText('metadata');
            $table->boolean('register')->default(false);
            $table->string('status', 40)->default('queued');
            $table->string('error_code', 80)->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'seedbox_id', 'source_hash'], 'seedbox_publication_source_unique');
            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seedbox_publications');
    }
};
