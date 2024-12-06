<?php

declare(strict_types=1);


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('rss');
        Schema::create('rss', function (Blueprint $table): void {
            $table->integer('id', true);
            $table->integer('position')->default(0);
            $table->string('name', 255)->default('Default');
            $table->integer('user_id')->default(1);
            $table->integer('staff_id')->default(0);
            $table->boolean('is_private')->default(0)->index();
            $table->boolean('is_torrent')->default(0)->index();
            $table->json('json_torrent');
            $table->softDeletes();
            $table->timestamps();
        });
    }
};
