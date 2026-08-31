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
      // H&R restriction (user can still seed)
if ($user->downloadpos === 'no' && $dto->left > 0 && $user->hit_and_run_count >= 20) {
    return $this->failure('You cannot download torrents until you reduce your Hit & Run count below 20.');
}

// General download restriction (staff ban, etc.)
if ($user->downloadpos === 'no') {
    return $this->failure('Your download privileges are revoked.');
}
        
//        if ($user->hit_and_run_count >= 20 && $dto->left > 0) {
//     return $this->failure('You cannot download torrents until you reduce your Hit & Run count below 20.');
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
            'peers'        => $announceService->givePeers(
                $result['peers'] ?? [],
                $dto->compact,
                $dto->noPeerId),
        ]))->withHeaders(['Content-Type' => 'text/plain']);
    }

    private function failure(string $reason)
{
    return response(
        Bencode::bencode([
            'failure reason' => $reason
        ]),
        200
    )->header('Content-Type', 'text/plain');
}
}

