<?php

namespace App\Services;

use App\DTO\AnnounceRequestDTO;
use App\Models\Peer;
use App\Models\History;
use App\Models\User;
use App\Models\Torrent;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AnnounceService
{
    /**
     * Handles peer events and updates DB models accordingly.
     */
    public function handleEvent(
        AnnounceRequestDTO $dto,
        Peer $peer,
        History $history,
        User $user,
        Torrent $torrent,
        float $deltaUp,
        float $deltaDn,
        callable $connectableChecker
    ) {
        $left  = $dto->left;
        $event = $dto->event;

        // Update connectable for started or completed peers
        if (in_array($event, ['started', 'completed'])) {
            $peer->connectable = $connectableChecker($peer->ip, $peer->port);
        }

        $now = Carbon::now();

        switch ($event) {
            case 'started':
                $peer->active = true;
                $peer->save();
                Cache::forget("torrent:{$torrent->info_hash}");
                break;

            case 'completed':
                $peer->left   = $left;
                $peer->seeder = $left == 0 ? 1 : 0;
                $peer->active = true;
                $peer->save();

                $history->active       = true;
                $history->seeder       = true;
                $history->completed_at = $now;
                $history->save();

                $user->uploaded   += $deltaUp;
                $user->downloaded += $deltaDn;
                $user->save();

                $torrent->times_completed++;
                $torrent->save();

                Cache::forget("torrent:{$torrent->info_hash}");
                Cache::forget("torrent:{$torrent->id}:peers:{$user->id}");
                break;

            case 'stopped':
                // Update seedtime before removing peer
                $this->updateSeedtime($history);
                $peer->active  = false;
                $peer->seeder  = 0;
                $peer->left    = $left;
                $peer->save();
                $peer->delete();


                $history->active            = false;
                $history->seeder            = false;
                $history->client_uploaded   = 0;
                $history->client_downloaded = 0;
                $history->save();

                $user->uploaded   += $deltaUp;
                $user->downloaded += $deltaDn;
                $user->save();

                Cache::forget("torrent:{$torrent->info_hash}");
                Cache::forget("torrent:{$torrent->id}:peers:{$user->id}");
                break;

            default:
                $peer->active = true;
                $peer->left   = $left;
                $peer->seeder = $left == 0 ? 1 : 0;
                $peer->save();

                $history->active = true;
                $history->seeder = ($left == 0);
                $history->save();

                $user->uploaded   += $deltaUp;
                $user->downloaded += $deltaDn;
                $user->save();

                Cache::forget("torrent:{$torrent->info_hash}");
                break;
        }

        // Always update client timestamp
        $peer->client_updated_at = $now;
        $peer->save();
    }

    /**
     * Updates seedtime based on last activity.
     */
    public function updateSeedtime(History $history): void
    {
        if ($history->seeder && $history->client_updated_at) {
            $elapsed = max(0, Carbon::now()->timestamp - $history->client_updated_at->timestamp);
            $history->seedtime += $elapsed;
        }
    }

    /**
     * Calculates modified upload/download based on freeleech/doubleleech.
     */
    public function calculateDelta(float $realUp, float $realDn, Peer $peer, History $history, Torrent $torrent, User $user, bool $isDouble = false, bool $isFree = false): array
    {
        $deltaUp = max(0, $realUp - ($peer->uploaded ?? 0));
        $deltaDn = max(0, $realDn - ($peer->downloaded ?? 0));

        $modUp = ($torrent->double || $isDouble) ? ($deltaUp * 2) : $deltaUp;
        $modDn = ($torrent->free || $isFree) ? 0 : $deltaDn;

        return [$modUp, $modDn];
    }
}
