<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
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
            $expectedSlug = Str::slug(str_replace('.', '-', $movie->name), '-'); // Generate the expected slug from the name

            // Check if the slug is null or doesn't match the expected slug
            if (!$movie->slug || $movie->slug !== $expectedSlug) {
                // Ensure the slug is unique by appending a number if it already exists
                $originalSlug = $expectedSlug;
                $count = 1;
                while (Torrent::where('slug', $expectedSlug)->exists()) {
                    $expectedSlug = $originalSlug . '-' . $count++;
                }

                // Set the new slug and save the movie
                $movie->slug = $expectedSlug;
                $movie->save();

                $this->info("Slug generated for movie: {$movie->name}");
            }
        }

        $this->info('Slugs generated for all movies!');
    }
}
