<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('torrent_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('personal_access_token_id')->nullable()->constrained()->nullOnDelete();
            $table->string('token_name', 80)->nullable();
            $table->string('torrent_name')->nullable();
            $table->string('method', 16);
            $table->string('status', 24)->default('processing');
            $table->string('error_code', 80)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['method', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_attempts');
    }
};
