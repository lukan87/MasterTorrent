<?php

namespace App\Console\Commands;

use App\Services\SeedboxPublishingReadiness;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class UploadApiPreflight extends Command
{
    protected $signature = 'upload-api:preflight';

    protected $description = 'Read-only check of upload API schema, storage and queue readiness.';

    public function handle(): int
    {
        $ready = true;
        foreach (['personal_access_tokens', 'api_token_secrets', 'upload_attempts', 'seedbox_publications'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->error('Missing required table: '.$table);
                $ready = false;
            }
        }
        if (! Schema::hasColumns('torrents', ['info_hash', 'tvdbid', 'season', 'episode'])) {
            $this->error('Required torrent columns are missing.');
            $ready = false;
        }
        $indexes = Schema::getIndexes('torrents');
        foreach (['info_hash', 'slug'] as $column) {
            if (! collect($indexes)->contains(fn ($index) => $index['unique'] && $index['columns'] === [$column])) {
                $this->error('Missing unique torrent index: '.$column);
                $ready = false;
            }
        }
        $path = storage_path('app/private');
        while (! is_dir($path) && dirname($path) !== $path) {
            $path = dirname($path);
        }
        if (! is_writable($path)) {
            $this->error('Private storage parent is not writable.');
            $ready = false;
        }
        if (! app(SeedboxPublishingReadiness::class)->ready()) {
            $this->warn('Seedbox publishing is disabled. Enable UPLOAD_API_SEEDBOX_PUBLISHING_ENABLED only after provider/worker checks; supported queue retry_after must be at least 360 seconds.');
        } else {
            $this->info('Verify a queue worker is running with a timeout above 180 seconds.');
        }
        if (! config('services.tmdb.key')) {
            $this->warn('TMDB metadata enrichment requires a configured TMDB_API_KEY; manual uploads remain available.');
        }
        $this->info('This check has not modified schema, configuration, files or torrents.');

        return $ready ? self::SUCCESS : self::FAILURE;
    }
}
