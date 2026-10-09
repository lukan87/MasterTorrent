<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['movies', 'series'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->boolean('online_enabled')->default(true));
        }
    }

    public function down(): void
    {
        foreach (['movies', 'series'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('online_enabled'));
        }
    }
};
