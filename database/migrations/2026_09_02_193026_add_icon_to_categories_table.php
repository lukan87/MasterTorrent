<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        // Movies
        DB::table('categories')
            ->where('name', 'like', '%Movies%')
            ->update(['icon' => 'bi bi-film']);

        // Music
        DB::table('categories')
            ->where('name', 'Music')
            ->update(['icon' => 'bi bi-music-note']);

        // HDTV / TV
        DB::table('categories')
            ->where(function ($query) {
                $query->where('name', 'like', '%HDTV%')
                    ->orWhere('name', 'like', 'TV%');
            })
            ->update(['icon' => 'bi bi-tv']);

        // Android Apps
        DB::table('categories')
            ->where('name', 'Android Apps')
            ->update(['icon' => 'bi bi-google-play']);

        // Software
        DB::table('categories')
            ->where('name', 'Software')
            ->update(['icon' => 'bi bi-code-slash']);

        // Sport
        DB::table('categories')
            ->where('name', 'Sport')
            ->update(['icon' => 'bi bi-scooter']);

        // Documentary
        DB::table('categories')
            ->where('name', 'like', 'Documentary%')
            ->update(['icon' => 'bi bi-smartwatch']);

        // Images
        DB::table('categories')
            ->where('name', 'Images')
            ->update(['icon' => 'bi bi-images']);

        // Diverse
        DB::table('categories')
            ->where('name', 'Diverse')
            ->update(['icon' => 'bi bi-shuffle']);

        // Games
        DB::table('categories')
            ->where('name', 'like', 'Games%')
            ->update(['icon' => 'bi bi-joystick']);
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
