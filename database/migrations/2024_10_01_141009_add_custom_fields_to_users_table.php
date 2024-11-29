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
        Schema::table('users', function (Blueprint $table) {
            // Add new fields with specified attributes
            $table->enum('acceptpm', ['yes', 'no'])->default('yes'); // Default is 'yes'
            $table->string('title', 60)->nullable(); // Varchar(60), nullable
            $table->enum('enabled', ['yes', 'no'])->default('yes'); // Default is 'yes'
            $table->enum('donor', ['yes', 'no'])->default('no'); // Default is 'no'
            $table->text('info')->nullable(); // Text field, nullable
            $table->ipAddress('IP')->nullable(); // IP address field, nullable
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
