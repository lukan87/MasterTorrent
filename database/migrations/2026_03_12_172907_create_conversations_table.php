<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_one')->constrained('users')->cascadeOnDelete();
    $table->foreignId('user_two')->constrained('users')->cascadeOnDelete();

    $table->string('subject')->nullable();

    $table->timestamp('last_message_at')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
