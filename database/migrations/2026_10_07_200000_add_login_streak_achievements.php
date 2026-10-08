<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('login_streak_days')->default(0);
            $table->date('last_login_streak_date')->nullable();
        });
        Schema::table('user_achievements', function (Blueprint $table) {
            $table->unsignedTinyInteger('vip_months_awarded')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('user_achievements', fn (Blueprint $table) => $table->dropColumn('vip_months_awarded'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['login_streak_days', 'last_login_streak_date']));
    }
};
