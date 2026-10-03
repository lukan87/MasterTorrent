<?php

use App\Services\SystemMessageService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(fn () => SystemMessageService::consolidateHistory());
        Schema::table('conversations', function (Blueprint $table) {
            $table->unique(['user_one', 'user_two'], 'conversations_participants_unique');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropUnique('conversations_participants_unique');
        });
        // Messages remain in their consolidated threads; no history is removed.
    }
};
