<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tv_show_follows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedInteger('tvmaze_id');
            $table->string('title');
            $table->string('imdbid')->nullable()->index();
            $table->string('tmdbid')->nullable()->index();
            $table->boolean('notify_upload')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'tvmaze_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tv_show_follows');
    }
};
