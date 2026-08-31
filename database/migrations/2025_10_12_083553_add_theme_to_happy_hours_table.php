<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('happy_hours', function (Blueprint $table) {
            $table->string('theme')->nullable()->after('free_download');
        });
    }

    public function down(): void
    {
        Schema::table('happy_hours', function (Blueprint $table) {
            $table->dropColumn('theme');
        });
    }
};
