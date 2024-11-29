<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Series;
use App\Models\Torrent;
use Illuminate\Support\Str;

class GenerateMovieSlugs extends Command
{
    protected $signature = 'movies:generate-slugs';
    protected $description = 'Generate slugs for all existing movies';

    public function handle()
    {
        $movies = Torrent::all();

        foreach ($movies as $movie) {
            // Check if slug already exists, to avoid overwriting
            if ($movie->slug) {
                $slug = Str::slug($movie->name);

        $name = str_replace('.', '-', $movie->name); // Replace dots with hyphens
        $slug = Str::slug($name, '-'); // Generate the slug

        // Ensure the slug is unique by appending a number if it already exists
        $originalSlug = $slug;
        $count = 1;
        while (Torrent::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

                // Set the slug and save the movie
                $movie->slug = $slug;
                $movie->save(); // Save changes to the database

                $this->info("Slug generated for movie: {$movie->name}");
            }
        }

        $this->info('Slugs generated for all movies!');
    }
}
