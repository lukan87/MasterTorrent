<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_bounce_events', function (Blueprint $table) {
            $table->id();

            // Unique fingerprint of the original Postfix bounce log event.
            // Prevents the same event being processed more than once.
            $table->string('fingerprint', 64)->unique();

            // Postfix queue ID when available.
            $table->string('queue_id', 50)->nullable()->index();

            $table->string('email')->index();

            // Enhanced SMTP status, e.g. 5.1.1
            $table->string('dsn', 20)->nullable()->index();

            $table->string('bounce_type', 50)->default('hard')->index();

            $table->text('message')->nullable();

            // Links to FileIplay records when a match was found.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('email_campaign_recipient_id')
                ->nullable()
                ->constrained('email_campaign_recipients')
                ->nullOnDelete();

            $table->timestamp('bounced_at')->nullable()->index();
            $table->timestamp('processed_at')->nullable()->index();

            $table->boolean('user_deleted')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_bounce_events');
    }
};