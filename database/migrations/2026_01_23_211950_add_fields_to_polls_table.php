<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
    $table->foreignId('user_id')
        ->nullable() // 👈 IMPORTANT
        ->after('id')
        ->constrained()
        ->cascadeOnDelete();

    $table->timestamp('expires_at')->nullable()->after('description');
    $table->boolean('is_active')->default(true)->after('expires_at');
    $table->softDeletes();
});

    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'expires_at',
                'is_active',
                'deleted_at',
            ]);
        });
    }
};
