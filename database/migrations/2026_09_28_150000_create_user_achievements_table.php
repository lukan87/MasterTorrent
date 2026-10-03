<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40);
            $table->unsignedBigInteger('threshold');
            $table->unsignedSmallInteger('tier');
            $table->decimal('balance_before', 14, 2);
            $table->decimal('bonus_awarded', 14, 2);
            $table->unsignedInteger('invites_awarded')->default(0);
            $table->timestamp('earned_at');
            $table->unique(['user_id', 'category', 'threshold']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
