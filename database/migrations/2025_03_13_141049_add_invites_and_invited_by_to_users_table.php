<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvitesAndInvitedByToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Adds the `invites` column (default value is 1)
            $table->integer('invites')->default(1);

            // Adds the `invited_by` column (nullable, foreign key to `users`)
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drops the columns if the migration is rolled back
            $table->dropColumn('invites');
            $table->dropForeign(['invited_by']);
            $table->dropColumn('invited_by');
        });
    }
}
