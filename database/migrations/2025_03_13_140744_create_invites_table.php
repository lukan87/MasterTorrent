<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitesTable extends Migration
{
    public function up()
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inviter_id')->constrained('users'); // Foreign key to users table
            $table->string('invite_code')->unique(); // Unique invite code
            $table->boolean('is_used')->default(0); // Default to 0 (not used)
            $table->boolean('is_expired')->default(false); // Default to false (not expired)
            $table->timestamps(); // Created_at, updated_at timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('invites');
    }
}

