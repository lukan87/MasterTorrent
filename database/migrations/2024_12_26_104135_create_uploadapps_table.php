<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('uploadapps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('internal_speed')->nullable(); // Assuming it's a URL
            $table->string('external_speed')->nullable(); // Assuming it's a URL
            $table->text('why_promoted');
            $table->text('external_sites')->nullable();
            $table->boolean('scene_access')->default(1);
            $table->boolean('know_torrents')->default(0);
            $table->boolean('understand_seeding')->default(1);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending'); // Status column
            $table->timestamps();

            // Foreign key constraints if needed
            $table->foreign('applicant_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploadapps');
    }
};
