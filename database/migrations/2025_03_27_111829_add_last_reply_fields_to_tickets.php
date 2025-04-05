<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('tickets', function (Blueprint $table) {
        $table->timestamp('last_replied_at')->nullable();
        $table->foreignId('last_replier_id')->nullable()->constrained('users')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('tickets', function (Blueprint $table) {
        $table->dropColumn(['last_replied_at', 'last_replier_id']);
    });
}
};
