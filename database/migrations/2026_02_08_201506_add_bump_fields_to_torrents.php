<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->timestamp('bumped_at')->nullable()->after('created_at');
            $table->unsignedBigInteger('bumped_by')->nullable()->after('bumped_at');

            $table->index('bumped_at');
            $table->index('bumped_by');
        });
    }

    public function down(): void
    {
        Schema::table('torrents', function (Blueprint $table) {
            $table->dropIndex(['bumped_at']);
            $table->dropIndex(['bumped_by']);
            $table->dropColumn(['bumped_at', 'bumped_by']);
        });
    }
};
