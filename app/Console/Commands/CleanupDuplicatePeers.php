<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicatePeers extends Command
{
    protected $signature = 'peers:cleanup-duplicates {--dry-run : Doar afiseaza, nu sterge nimic}';
    protected $description = 'Sterge peers dublati si randuri invalide din tabela peers';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Cleanup peers: start' . ($dryRun ? ' (dry-run)' : ''));

        // 1) Randuri invalide (peer_id NULL sau gol)
        $invalidQuery = DB::table('peers')
            ->whereNull('peer_id')
            ->orWhere('peer_id', '=', '');

        $invalidCount = (clone $invalidQuery)->count();

        if (!$dryRun && $invalidCount > 0) {
            $invalidQuery->delete();
        }

        $this->line("Invalid peers (peer_id null/empty): " . $invalidCount . ($dryRun ? ' (would delete)' : ' deleted'));

        // 2) Dubluri: torrent_id + user_id + peer_id
        // pastram ultimul (cel mai nou) si stergem restul
        $duplicates = DB::table('peers')
            ->select('torrent_id', 'user_id', 'peer_id', DB::raw('COUNT(*) as c'))
            ->whereNotNull('peer_id')
            ->where('peer_id', '!=', '')
            ->groupBy('torrent_id', 'user_id', 'peer_id')
            ->having('c', '>', 1)
            ->get();

        $groups = $duplicates->count();
        $toDeleteIds = [];

        foreach ($duplicates as $dup) {
            $ids = DB::table('peers')
                ->where('torrent_id', $dup->torrent_id)
                ->where('user_id', $dup->user_id)
                ->where('peer_id', $dup->peer_id)
                ->orderByDesc(DB::raw('COALESCE(client_updated_at, updated_at)'))
                ->orderByDesc('id')
                ->pluck('id')
                ->all();

            // pastram primul (cel mai nou), stergem restul
            array_shift($ids);
            foreach ($ids as $id) {
                $toDeleteIds[] = $id;
            }
        }

        $deleteCount = count($toDeleteIds);

        if ($deleteCount > 0) {
            if ($dryRun) {
                $this->line("Duplicate groups: {$groups}");
                $this->line("Duplicate rows: {$deleteCount} (would delete)");
            } else {
                // stergere in batch ca sa nu moara pe liste mari
                $chunks = array_chunk($toDeleteIds, 2000);
                foreach ($chunks as $chunk) {
                    DB::table('peers')->whereIn('id', $chunk)->delete();
                }
                $this->line("Duplicate groups: {$groups}");
                $this->line("Duplicate rows: {$deleteCount} deleted");
            }
        } else {
            $this->line("Duplicate groups: {$groups}");
            $this->line("Duplicate rows: 0");
        }

        $this->info('Cleanup peers: done');

        return self::SUCCESS;
    }
}
