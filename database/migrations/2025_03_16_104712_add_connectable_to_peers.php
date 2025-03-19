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
        Schema::table('peers', function (Blueprint $table): void {
            $table->boolean('connectable')->default(0)->after('port'); // Change 'some_column' to an appropriate existing column
        });
    }

    public function down(): void
    {
        Schema::table('peers', function (Blueprint $table): void {
            $table->dropColumn('connectable');
        });
    }
};
