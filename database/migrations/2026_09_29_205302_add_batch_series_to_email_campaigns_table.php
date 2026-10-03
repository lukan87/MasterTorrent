<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->uuid('batch_group_id')
                ->nullable()
                ->after('id')
                ->index();

            $table->unsignedInteger('batch_number')
                ->default(1)
                ->after('batch_group_id');

            $table->unsignedInteger('batch_size')
                ->nullable()
                ->after('batch_number');

            $table->foreignId('previous_campaign_id')
                ->nullable()
                ->after('batch_size')
                ->constrained('email_campaigns')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('email_campaigns', function (Blueprint $table) {
            $table->dropForeign([
                'previous_campaign_id',
            ]);

            $table->dropIndex([
                'batch_group_id',
            ]);

            $table->dropColumn([
                'batch_group_id',
                'batch_number',
                'batch_size',
                'previous_campaign_id',
            ]);
        });
    }
};