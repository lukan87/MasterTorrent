<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('torrent_images', function (Blueprint $table) {
            $table->string('fallback')->nullable()->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('torrent_images', function (Blueprint $table) {
            $table->dropColumn('fallback');
        });
    }
};
