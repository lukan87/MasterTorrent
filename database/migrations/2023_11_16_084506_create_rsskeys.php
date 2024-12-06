<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('rsskeys', function (Blueprint $table): void {
            $table->increments('id');
            // Change to unsignedBigInteger
            $table->unsignedBigInteger('user_id');
            $table->string('content')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('deleted_at')->nullable();

            // Add the foreign key constraint with cascadeOnUpdate
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
        });

        // Insert data into rsskeys table
        DB::table('users')
            ->lazyById()
            ->each(fn ($user) => DB::table('rsskeys')->insert([
                'user_id'    => $user->id,
                'content'    => $user->rsskey,
                'created_at' => now(),
            ]));
    }
};
