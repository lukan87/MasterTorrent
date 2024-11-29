<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->string('tmdb_type')->nullable(); // Add the tmdb_type column
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->dropColumn('tmdb_type'); // Remove the tmdb_type column if rolling back
        });
    }
};
