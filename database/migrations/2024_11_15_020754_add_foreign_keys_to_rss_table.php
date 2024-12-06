<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rss', function (Blueprint $table): void {
            // Make sure userID is unsignedBigInteger
            $table->unsignedBigInteger('userID')->change();

            // Add the foreign key constraint
            $table->foreign('userID', 'rss_user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }
};

