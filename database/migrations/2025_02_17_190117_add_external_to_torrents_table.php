<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('torrents', function (Blueprint $table) {
            // Adding the 'external' column
            $table->boolean('external')->default(0)->after('sticky');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('torrents', function (Blueprint $table) {
            // Dropping the 'external' column
            $table->dropColumn('external');
        });
    }
};
