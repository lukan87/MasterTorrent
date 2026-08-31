<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {

            $table->id();

            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('color')->nullable();

            $table->timestamps();
        });

        DB::table('ticket_categories')->insert([
            [
                'name' => 'Technical Issue',
                'icon' => 'bi-tools',
                'color' => '#3b82f6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Account Problem',
                'icon' => 'bi-person',
                'color' => '#ef4444',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Download / Upload Problem',
                'icon' => 'bi-arrow-down-up',
                'color' => '#f59e0b',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Torrent Problem',
                'icon' => 'bi-cloud-download',
                'color' => '#10b981',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'User Report',
                'icon' => 'bi-flag',
                'color' => '#8b5cf6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Other',
                'icon' => 'bi-question-circle',
                'color' => '#6b7280',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_categories');
    }
};