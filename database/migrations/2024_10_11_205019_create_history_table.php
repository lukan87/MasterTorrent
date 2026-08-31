<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index('history_user_id_foreign');
            $table->string('agent')->nullable();
            $table->string('info_hash')->index('info_hash');
            $table->bigInteger('uploaded')->unsigned()->nullable();
            $table->bigInteger('actual_uploaded')->unsigned()->nullable();
            $table->bigInteger('client_uploaded')->unsigned()->nullable();
            $table->bigInteger('downloaded')->unsigned()->nullable();
            $table->bigInteger('actual_downloaded')->unsigned()->nullable();
            $table->bigInteger('client_downloaded')->unsigned()->nullable();
            $table->boolean('seeder')->default(0);
            $table->boolean('active')->default(0);
            $table->bigInteger('seedtime')->unsigned()->default(0);
            $table->timestamps();
            $table->dateTime('completed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history');
    }
};
