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
        Schema::table('requests', function (Blueprint $table) {
            $table->unsignedBigInteger('requested_by')->after('id')->nullable(); // Add the column
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade'); // Add foreign key
        });
    }

    public function down()
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign(['requested_by']); // Remove the foreign key
            $table->dropColumn('requested_by'); // Drop the column
        });
    }

};
