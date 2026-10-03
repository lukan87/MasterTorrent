<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();

            // Email content
            $table->string('subject');
            $table->longText('body');

            // Audience selected in the admin composer
            $table->string('target', 50);

            // Optional filters such as selected user classes
            $table->json('filters')->nullable();

            // Campaign progress
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('queued_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);

            // queued | sending | completed | completed_with_failures | failed
            $table->string('status', 50)->default('queued');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_campaigns');
    }
};