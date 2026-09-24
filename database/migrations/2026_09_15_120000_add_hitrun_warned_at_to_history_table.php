<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->timestamp('hitrun_warned_at')->nullable()->after('hitrun_removed_at');
        });
    }

    public function down()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->dropColumn('hitrun_warned_at');
        });
    }
};