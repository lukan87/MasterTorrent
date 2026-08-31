<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_application_votes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('application_id')
                ->constrained('upload_applications')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->enum('vote', ['approve', 'reject']);

            $table->timestamps();

            // Prevent multiple votes from same staff
            $table->unique(['application_id','user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_application_votes');
    }
};