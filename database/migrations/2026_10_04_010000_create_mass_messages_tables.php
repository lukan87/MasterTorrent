<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mass_messages', function (Blueprint $table) {
            $table->id();
            // Snapshots retain attribution even if an account is renamed or deleted.
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_name');
            $table->unsignedBigInteger('sender_id');
            $table->string('sender_name');
            $table->boolean('send_as_system')->default(false);
            $table->string('subject');
            $table->text('body');
            $table->json('user_classes');
            $table->string('status', 20)->default('queued')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mass_message_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mass_message_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('receiver_id');
            $table->string('receiver_name');
            $table->foreignId('message_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('pending');
            $table->boolean('was_read')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->unique(['mass_message_id', 'receiver_id']);
            $table->index(['mass_message_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_message_deliveries');
        Schema::dropIfExists('mass_messages');
    }
};
