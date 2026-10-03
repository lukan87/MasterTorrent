<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaign_recipients', function (Blueprint $table) {
            $table->id();

            $table->foreignId('email_campaign_id')
                ->constrained('email_campaigns')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
             * Snapshot the recipient details.
             *
             * This means campaign history still shows who the email
             * was sent to even if the user's account/email changes later.
             */
            $table->string('name')->nullable();
            $table->string('email');

            // queued | processing | sent | failed
            $table->string('status', 30)->default('queued');

            $table->unsignedTinyInteger('attempts')->default(0);

            // Stores the final error if delivery fails.
            $table->text('error')->nullable();

            $table->timestamp('queued_at')->nullable();
            $table->timestamp('processing_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();

            /*
             * Prevent the same user being added twice to one campaign.
             */
            $table->unique(
                ['email_campaign_id', 'user_id'],
                'email_campaign_user_unique'
            );

            /*
             * Useful for campaign detail/progress screens.
             */
            $table->index(
                ['email_campaign_id', 'status'],
                'email_campaign_status_index'
            );

            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaign_recipients');
    }
};