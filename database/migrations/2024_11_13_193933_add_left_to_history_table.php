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
        Schema::table('history', function (Blueprint $table) {
            $table->bigInteger('left')->default(0);
        });
    }

    public function down()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->dropColumn('left');
        });
    }
};
