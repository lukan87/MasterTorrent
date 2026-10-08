<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['movies', 'series'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->decimal('imdb_rating', 3, 1)->nullable();
                $table->unsignedBigInteger('imdb_votes')->nullable();
                $table->decimal('recommendation_score', 6, 4)->default(0)->index();
                $table->timestamp('ratings_updated_at')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        foreach (['movies', 'series'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropIndex(['recommendation_score']);
                $table->dropIndex(['ratings_updated_at']);
                $table->dropColumn(['imdb_rating', 'imdb_votes', 'recommendation_score', 'ratings_updated_at']);
            });
        }
    }
};
