<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->boolean('hot')->default(false)->index();
            $table->double('hot_score')->default(0);
            $table->timestamp('hot_activity_at')->nullable();
            $table->timestamp('hot_until')->nullable();
            $table->timestamp('hot_cooldown_until')->nullable();
            $table->unsignedBigInteger('hot_sample_bytes')->nullable();
            $table->unsignedInteger('hot_sample_leechers')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->dropIndex(['hot']);
            $table->dropColumn(['hot', 'hot_score', 'hot_activity_at', 'hot_until',
                'hot_cooldown_until', 'hot_sample_bytes', 'hot_sample_leechers']);
        });
    }
};
