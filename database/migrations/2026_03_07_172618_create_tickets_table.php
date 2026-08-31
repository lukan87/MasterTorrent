<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');

            $table->string('title');
            $table->text('description');

            $table->enum('priority',['Low','Medium','High','Critical'])->default('Medium');

            $table->enum('status',[
                'Open',
                'Waiting Staff',
                'Waiting User',
                'Resolved',
                'Closed'
            ])->default('Open');

            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('claimed_by')->nullable();

            $table->timestamp('last_replied_at')->nullable();
            $table->unsignedBigInteger('last_replier_id')->nullable();

            $table->boolean('is_locked')->default(false);

            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};