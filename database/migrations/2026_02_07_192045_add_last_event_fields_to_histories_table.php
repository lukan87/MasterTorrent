<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::table('history', function (Blueprint $table) {
    $table->timestamp('last_event_at')->nullable()->after('updated_at');
    $table->string('last_event', 20)->nullable()->after('last_event_at');
    $table->index(['user_id', 'last_event', 'last_event_at']);
});
    }

    public function down(): void
    {
        Schema::table('history', function (Blueprint $table) {
            $table->dropColumn(['last_event_at', 'last_event']);
        });
    }
};
