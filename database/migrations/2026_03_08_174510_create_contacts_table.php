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
       Schema::create('contacts', function (Blueprint $table) {

    $table->id();

    $table->string('name');
    $table->string('email')->index();
    $table->string('subject');

    $table->text('message');

    $table->text('staff_reply')->nullable();

    $table->unsignedBigInteger('answered_by')->nullable();
    $table->timestamp('answered_at')->nullable();

    $table->boolean('is_answered')->default(false);

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
