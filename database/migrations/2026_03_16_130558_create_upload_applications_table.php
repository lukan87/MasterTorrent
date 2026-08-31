<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_applications', function (Blueprint $table) {

            $table->id();

            $table->foreignId('applicant_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('internal_speed')->nullable();
            $table->string('external_speed')->nullable();

            $table->text('why_promoted');

            $table->text('external_sites')->nullable();

            $table->boolean('scene_access')->default(false);
            $table->boolean('know_torrents')->default(false);
            $table->boolean('understand_seeding')->default(false);

            // New powerful fields
            $table->text('experience')->nullable();
            $table->text('content_plan')->nullable();

            $table->enum('status', [
                'pending',
                'discussion',
                'voting',
                'accepted',
                'rejected'
            ])->default('pending');

            $table->integer('votes_for')->default(0);
            $table->integer('votes_against')->default(0);

            $table->timestamp('decision_at')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_applications');
    }
};