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
            $table->unsignedBigInteger('torrent_id')->after('id'); // Adjust the position as needed

            // Optional: Add a foreign key constraint if needed
            // $table->foreign('torrent_id')->references('id')->on('torrents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('history', function (Blueprint $table) {
            $table->dropColumn('torrent_id'); // Remove the column on rollback
        });
    }
};
