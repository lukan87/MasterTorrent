<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryIdToTorrentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('torrents', function (Blueprint $table) {
            // Add the foreign key for the category_id
            $table->unsignedBigInteger('category_id')->nullable()->after('owner');


            // Add a foreign key constraint to categories table
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('torrents', function (Blueprint $table) {
            // Drop the foreign key and column
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
}
