<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_token_secrets', function (Blueprint $table) {
            $table->unsignedBigInteger('personal_access_token_id')->primary();
            $table->text('encrypted_token');
            $table->foreign('personal_access_token_id')->references('id')->on('personal_access_tokens')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_token_secrets');
    }
};
