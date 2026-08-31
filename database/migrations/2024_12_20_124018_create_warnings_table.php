<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warnings', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedBigInteger('user_id')->index('warnings_user_id_foreign');
            $table->unsignedBigInteger('warned_by')->index('warnings_warned_by_foreign');
            $table->unsignedBigInteger('torrent')->nullable()->index('warnings_torrent_foreign');
            $table->text('reason');
            $table->dateTime('expires_on')->nullable();
            $table->boolean('active')->default(0);
            $table->timestamps();
        });

        // Add foreign key constraints
        Schema::table('warnings', function (Blueprint $table): void {
            $table->foreign('torrent')->references('id')->on('torrents')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('warned_by')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        // Add soft delete and deleted_by column
        Schema::table('warnings', function (Blueprint $table): void {
            $table->unsignedBigInteger('deleted_by')->after('active')->nullable();
            $table->softDeletes()->after('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warnings');
    }
};
