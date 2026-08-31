<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_categories', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable();

            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_private')->default(false);

            $table->timestamps();

            $table->index(['position', 'is_private']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_categories');
    }
};