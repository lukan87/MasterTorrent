<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Category name
            $table->string('image')->nullable(); // Image URL
            $table->timestamps();
        });

        // Insert default categories with ids and image paths
        DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Movies: Anime', 'image' => 'images/categories/anime.png'],
            ['id' => 2, 'name' => 'Movies: Anime-Ro', 'image' => 'images/categories/anime-ro.png'],
            ['id' => 5, 'name' => 'Movies: BluRay', 'image' => 'images/categories/bluray.png'],
            ['id' => 6, 'name' => 'Movies: BluRay-Ro', 'image' => 'images/categories/bluray-ro.png'],
            ['id' => 9, 'name' => 'Movies: DVD', 'image' => 'images/categories/dvd.png'],
            ['id' => 10, 'name' => 'Movies: DVD-Ro', 'image' => 'images/categories/dvd-ro.png'],
            ['id' => 11, 'name' => 'Movies: HD', 'image' => 'images/categories/hd.png'],
            ['id' => 12, 'name' => 'Movies: HD-Ro', 'image' => 'images/categories/hd-ro.png'],
            ['id' => 13, 'name' => 'HDTV Episodes', 'image' => 'images/categories/hdtve.png'],
            ['id' => 14, 'name' => 'HDTV Episodes-Ro', 'image' => 'images/categories/hdtve-ro.png'],
            ['id' => 16, 'name' => 'Movies: Old', 'image' => 'images/categories/oldies.png'],
            ['id' => 17, 'name' => 'Movies: Old-Ro', 'image' => 'images/categories/oldies-ro.png'],
            ['id' => 18, 'name' => 'Movies: Pack', 'image' => 'images/categories/pack.png'],
            ['id' => 19, 'name' => 'Movies: Pack-Ro', 'image' => 'images/categories/pack-ro.png'],
            ['id' => 20, 'name' => 'TV Episodes', 'image' => 'images/categories/tvepisode.png'],
            ['id' => 21, 'name' => 'TV Episodes-Ro', 'image' => 'images/categories/tvepisode-ro.png'],
            ['id' => 22, 'name' => 'RoContent', 'image' => 'images/categories/rocontent.png'],
            ['id' => 24, 'name' => 'Movies: XVID', 'image' => 'images/categories/xvid.png'],
            ['id' => 25, 'name' => 'Movies: XVID-Ro', 'image' => 'images/categories/xvid-ro.png'],
            ['id' => 26, 'name' => 'Software', 'image' => 'images/categories/soft.png'],
            ['id' => 27, 'name' => 'Movies: XXX', 'image' => 'images/categories/xxx.png'],
            ['id' => 28, 'name' => 'Music', 'image' => 'images/categories/music.png'],
            ['id' => 30, 'name' => 'Games: PC-ISO', 'image' => 'images/categories/pciso.png'],
            ['id' => 31, 'name' => 'Movies:4K', 'image' => 'images/categories/4k.png'],
            ['id' => 32, 'name' => 'Movies:4KRO', 'image' => 'images/categories/4kRO.png'],
            ['id' => 33, 'name' => 'Games: Pack', 'image' => 'images/categories/gpack.png'],
            ['id' => 34, 'name' => 'XXX Pack', 'image' => 'images/categories/packxxx.jpeg'],
            ['id' => 42, 'name' => 'Sport', 'image' => 'images/categoriesNORMALE/sport.png'],
            ['id' => 43, 'name' => 'Documents', 'image' => 'images/categories/docs.png'],
            ['id' => 44, 'name' => 'Images', 'image' => 'images/categories/images.png'],
            ['id' => 49, 'name' => 'Diverse', 'image' => 'images/categories/misc.png'],
            ['id' => 51, 'name' => 'Android Apps ', 'image' => 'images/categoriesNORMALE/android.png'],
            ['id' => 54, 'name' => 'Movies/WEB-DL', 'image' => 'images/categories/web-DL.png'],
            ['id' => 55, 'name' => 'Movies/WEB-DL RO', 'image' => 'images/categories/web-DLRO.png'],
            ['id' => 56, 'name' => 'Documentary', 'image' => 'images//categoriesLF/doc.png'],
            ['id' => 57, 'name' => 'Documentary-Ro', 'image' => 'images/categoriesLF/doc-ro.png'],
            ['id' => 60, 'name' => 'ImagesXXX', 'image' => 'images/categories/xxximgset.png'],
            ['id' => 81, 'name' => 'Movies: x265-Ro', 'image' => 'images/categories/x265ro.png'],
            ['id' => 82, 'name' => 'Movies: x265', 'image' => 'images/categoriesNORMALE/x265.png'],
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
