<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->unsignedBigInteger('tvdbid')->nullable()->index();
            $table->unsignedSmallInteger('season')->nullable();
            $table->unsignedSmallInteger('episode')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->dropIndex(['tvdbid']);
            $table->dropColumn(['tvdbid', 'season', 'episode']);
        });
    }
};
