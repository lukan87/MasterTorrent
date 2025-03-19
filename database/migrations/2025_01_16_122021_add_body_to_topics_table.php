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
        Schema::table('topics', function (Blueprint $table) {
            $table->text('body')->nullable(); // Add a body field
        });
    }
    
    public function down()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn('body');
        });
    }
    
};
