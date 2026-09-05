<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('forum_topic_views', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('topic_id')
                ->constrained('forum_topics')
                ->cascadeOnDelete();

            /*
             * updated_at represents the last time
             * this user visited the topic.
             */
            $table->timestamps();

            /*
             * One record per user/topic combination.
             */
            $table->unique([
                'user_id',
                'topic_id',
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forum_topic_views');
    }
};