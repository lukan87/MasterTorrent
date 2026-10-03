<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->string('bounce_type', 50)
                ->nullable()
                ->after('error');

            $table->string('bounce_code', 50)
                ->nullable()
                ->after('bounce_type');

            $table->text('bounce_message')
                ->nullable()
                ->after('bounce_code');

            $table->timestamp('bounced_at')
                ->nullable()
                ->after('bounce_message');

            $table->timestamp('user_deleted_at')
                ->nullable()
                ->after('bounced_at');

            $table->index('bounce_type');
            $table->index('bounced_at');
        });
    }

    public function down(): void
    {
        Schema::table('email_campaign_recipients', function (Blueprint $table) {
            $table->dropIndex(['bounce_type']);
            $table->dropIndex(['bounced_at']);

            $table->dropColumn([
                'bounce_type',
                'bounce_code',
                'bounce_message',
                'bounced_at',
                'user_deleted_at',
            ]);
        });
    }
};