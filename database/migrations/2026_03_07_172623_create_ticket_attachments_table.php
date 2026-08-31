<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_attachments', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('ticket_response_id');

            $table->string('file_path');
            $table->string('file_name');

            $table->timestamps();

            $table->index('ticket_response_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_attachments');
    }
};