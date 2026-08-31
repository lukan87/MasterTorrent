<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('forum_categories')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('slug');

            $table->unsignedInteger('views')->default(0);

            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);

            $table->unsignedBigInteger('last_post_id')->nullable();

            $table->timestamps();

            $table->index(['category_id', 'is_pinned']);
            $table->index(['user_id']);
            $table->index(['updated_at']);

            $table->unique(['category_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_topics');
    }
};