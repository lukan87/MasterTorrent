<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('torrents', function (Blueprint $table) {
        $table->string('file_signature', 40)->nullable()->index();
    });
}

public function down(): void
{
    Schema::table('torrents', function (Blueprint $table) {
        $table->dropColumn('file_signature');
    });
}
};
