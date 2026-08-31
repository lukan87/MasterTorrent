<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('body');

            // Author
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Control visibility
            $table->boolean('is_active')->default(true);

            // Scheduling
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // UI / sorting
            $table->string('type')->default('info'); // info, warning, success
            $table->integer('priority')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};