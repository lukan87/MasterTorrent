<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ticket_responses', function (Blueprint $table) {
            $table->string('file_path')->nullable(); // Allows for null values if no file is uploaded
        });
    }
    
    public function down()
    {
        Schema::table('ticket_responses', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
    
};
