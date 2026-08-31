<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {

        Schema::create('contact_messages', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('contact_id');

            $table->string('sender_type'); 
            // guest or staff

            $table->unsignedBigInteger('staff_id')->nullable();

            $table->text('message');

            $table->string('ip')->nullable();

            $table->timestamps();

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->cascadeOnDelete();

        });

    }

    public function down()
    {
        Schema::dropIfExists('contact_messages');
    }

};