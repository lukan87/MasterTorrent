<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seedboxes', function (Blueprint $table) {
            $table->enum('auth_type', ['basic', 'digest', 'session'])->default('basic')->change();
        });
    }

    public function down(): void
    {
        // Require an explicit choice instead of silently changing authentication.
        if (DB::table('seedboxes')->where('auth_type', 'session')->exists()) {
            throw new RuntimeException('Change session seedboxes to Basic/Digest before rolling back this migration.');
        }
        Schema::table('seedboxes', function (Blueprint $table) {
            $table->enum('auth_type', ['basic', 'digest'])->default('basic')->change();
        });
    }
};
