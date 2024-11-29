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



            $table->enum('uploadpos', ['yes', 'no'])->default('no');
            $table->enum('downloadpos', ['yes', 'no'])->default('no');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->decimal('seedbonus', 5, 1)->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                 'uploadpos',
                'downloadpos', 'gender', 'seedbonus'
            ]);
        });
    }
};
