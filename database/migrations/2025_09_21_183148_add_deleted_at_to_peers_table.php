<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peers', function (Blueprint $table) {
            $table->softDeletes()->after('active'); // adds 'deleted_at' column
        });
    }

    public function down(): void
    {
        Schema::table('peers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
