<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_events', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('event');

            $table->timestamps();

            $table->index('ticket_id');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_events');
    }
};