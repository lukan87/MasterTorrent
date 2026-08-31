<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('seedboxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Friendly label, e.g. "Home Seedbox"
            $table->string('address'); // Full URL to ruTorrent endpoint
            $table->enum('auth_type', ['basic', 'digest'])->default('basic');
            $table->string('username'); // required
            $table->text('password');   // encrypted with Crypt::encryptString
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seedboxes');
    }
};
