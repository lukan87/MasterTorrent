<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Performance indexes for the conversation-based messaging system.
     *
     * - conversations: accelerate "my conversations" + ordering by last_message_at
     * - messages:      accelerate unread counts (receiver_id + is_read)
     *                  and thread reads (conversation_id + created_at)
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->index(['user_one', 'last_message_at'], 'conv_user_one_last_idx');
            $table->index(['user_two', 'last_message_at'], 'conv_user_two_last_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index(['receiver_id', 'is_read'], 'msg_receiver_read_idx');
            $table->index(['conversation_id', 'created_at'], 'msg_conversation_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropIndex('conv_user_one_last_idx');
            $table->dropIndex('conv_user_two_last_idx');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('msg_receiver_read_idx');
            $table->dropIndex('msg_conversation_created_idx');
        });
    }
};