<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('history', function (Blueprint $table) {

            $table->timestamp('hitrun_removed_at')
                  ->nullable()
                  ->after('hitrun')
                  ->index();

        });
    }

    public function down(): void
    {
        Schema::table('history', function (Blueprint $table) {

            $table->dropColumn('hitrun_removed_at');

        });
    }
};