<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('happy_hour_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->boolean('automatic_enabled')->default(false);
        });
        // Preserve the legacy scheduler's interpretation at migration time.
        $latest = DB::table('happy_hours')->orderByDesc('id')->first();
        DB::table('happy_hour_settings')->insert([
            'id' => 1,
            'automatic_enabled' => $latest && $latest->automatic && $latest->active,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('happy_hour_settings');
    }
};
