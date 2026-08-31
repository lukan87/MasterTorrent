<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->id();

            // User who created the invite
            $table->unsignedBigInteger('inviter_id');

            // User who eventually used the invite
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('invite_code', 255)->unique();

            $table->boolean('is_used')->default(false);
            $table->boolean('is_expired')->default(false);

            $table->timestamps();

            $table->index('inviter_id');
            $table->index('user_id');

            $table->foreign('inviter_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invites');
    }
};