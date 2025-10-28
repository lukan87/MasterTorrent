<?php

namespace App\Http\Controllers;

use App\DTO\AnnounceRequestDTO;
use App\Models\User;
use App\Services\AnnounceService;
use App\Services\Bencode;
use Illuminate\Http\Request;

class AnnounceController extends Controller
{
    public function announce(Request $request, $passkey)
    {
        $agent = $request->server('HTTP_USER_AGENT') ?: "Unknown";
        $dto   = new AnnounceRequestDTO($request);

        $user = User::where('passkey', $passkey)->first();
        if (!$user) return $this->failure('Passkey invalid');
        if ($user->enabled === 'no') return $this->failure('Your account has been disabled. Please contact staff!');
        if ($user->downloadpos === 'no') return $this->failure('Your download privileges are revoked');
        if ($user->hit_and_run_count > 20) return $this->failure('You cannot download any torrents due to more than 20 hit and runs.');

        // --- STEP 2: Check banned clients ---
        // $bannedClients = config('clients.banned', []);
        // foreach ($bannedClients as $banned) {
        //     if (stripos($agent, $banned) !== false) {
        //         return $this->failure("Your client ($agent) is not allowed on this tracker.");
        //     }
        // }

        $ip = $request->ip() ?: $request->server('REMOTE_ADDR');

        $announceService = app(AnnounceService::class);
        $result = $announceService->handleAnnounce($dto, $user, $ip, $agent);

        if (isset($result['failure reason'])) {
            return $this->failure($result['failure reason']);
        }

        return response(Bencode::bencode([
            'interval'     => config('settings.announce_interval'),
            'min interval' => config('settings.min_interval'),
            'tracker_id'   => $result['tracker_id'],
            'complete'     => $result['torrent']->seeders,
            'incomplete'   => $result['torrent']->leechers,
            'downloaded'   => $result['torrent']->times_completed,
            'peers'        => $announceService->givePeers($result['peers'], $dto->compact, $dto->noPeerId),
        ]))->withHeaders(['Content-Type' => 'text/plain']);
    }

    private function failure(string $reason)
    {
        return response(Bencode::bencode(['failure reason' => $reason]), 200, ['Content-Type' => 'text/plain']);
    }
}

