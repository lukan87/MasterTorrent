<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the new columns
            $table->string('passkey', 32)->unique(); // Add a unique 32-char passkey
            $table->enum('enabled', ['yes', 'no'])->default('yes'); // Add enabled column with 'yes'/'no'
            $table->string('title', 60)->nullable(); // Add title with max 60 chars
            $table->unsignedBigInteger('uploaded')->default(0); // Add uploaded column
            $table->unsignedBigInteger('downloaded')->default(0); // Add downloaded column
            $table->enum('donor', ['yes', 'no'])->default('no'); // Add donor column with default 'no'
            $table->enum('uploadpos', ['yes', 'no'])->default('no'); // Add uploadpos with default 'no'
            $table->enum('downloadpos', ['yes', 'no'])->default('no'); // Add downloadpos with default 'no'
            $table->enum('gender', ['male', 'female'])->nullable(); // Add gender column
            $table->decimal('seedbonus', 5, 1)->default(0.0); // Add seedbonus with decimal (5,1)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the new columns if rolling back the migration
            $table->dropColumn([
                'passkey', 'enabled', 'title', 'uploaded', 'downloaded',
                'donor', 'uploadpos', 'downloadpos', 'gender', 'seedbonus'
            ]);
        });
    }
};
