<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            // Check if the column exists before adding it
            if (!Schema::hasColumn('topics', 'forum_category_id')) {
                $table->unsignedBigInteger('forum_category_id')->after('id'); // Adjust position as needed
            }

            // Add the foreign key constraint
            $table->foreign('forum_category_id')
                ->references('id')
                ->on('forum_categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('topics', function (Blueprint $table) {
            // Drop the foreign key and the column
            if (Schema::hasColumn('topics', 'forum_category_id')) {
                $table->dropForeign(['forum_category_id']);
                $table->dropColumn('forum_category_id');
            }
        });
    }
};
