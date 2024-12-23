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
        $table->integer('hit_and_run_count')->default(0); // Adding the column with default value 0
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('hit_and_run_count'); // Rollback functionality
    });
}
};
