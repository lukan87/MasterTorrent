<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peers', function (Blueprint $table) {
            $table->timestamp('connectable_checked_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('peers', function (Blueprint $table) {
            $table->dropColumn('connectable_checked_at');
        });
    }
};
