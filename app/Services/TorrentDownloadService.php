<?php

namespace App\Services;

use App\Models\Torrent;
use App\Models\User;
use App\Models\UserSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Bencode;
use Illuminate\Support\Facades\Redis;

class TorrentDownloadService
{
    public function handleDownload(Request $request, $id, $slug)
    {
        
        $torrent = $this->findTorrent($id, $slug);

        
        $user = $request->user();

       
        if (!$user) {
            throw new \Exception('Authentication required.');
        }

        
        $slotType = $this->getSlotType($request);

        
        if ($this->hasExistingSlot($user, $torrent, $slotType)) {
            return redirect()->route('torrents.show', ['id' => $torrent->id, 'slug' => $torrent->slug])
                ->with('error', 'You have already downloaded this torrent as ' . ucfirst($slotType) . '.');
        }

       
        if ($slotType) {
            $this->assignSlot($user, $torrent, $slotType);
        }

       
        return $this->prepareTorrentResponse($torrent, $user);
    }

   protected function findTorrent($id, $slug): Torrent
{
    $query = Torrent::where('id', $id)
        ->where('slug', $slug);

    
    if (auth()->check() &&
        auth()->user()->user_class >= \App\Models\UserClass::ADMIN) {

        $query->withTrashed();
    }

    $torrent = $query->firstOrFail();

    
    if ($torrent->trashed() &&
        auth()->user()->user_class < \App\Models\UserClass::ADMIN) {

        abort(403, 'This torrent has been deleted.');
    }

    return $torrent;
}

    protected function getSlotType(Request $request): ?string
    {
        if ($request->query('free')) {
            return 'free';
        }
        if ($request->query('double')) {
            return 'double';
        }
        return null;
    }

    protected function hasExistingSlot(User $user, Torrent $torrent, ?string $slotType): bool
    {
        if (!$slotType) return false;

        $existingSlot = UserSlot::where('user_id', $user->id)
            ->where('torrent_id', $torrent->id)
            ->where(function ($query) {
                $query->where('free', 1)
                      ->orWhere('double', 1);
            })
            ->first();

        return $existingSlot && ($slotType === 'free' || $slotType === 'double');
    }

    protected function assignSlot(User $user, Torrent $torrent, string $slotType): void
    {
        DB::transaction(function () use ($user, $torrent, $slotType) {
            
            if ($user->slots <= 0) {
                throw new \Exception('No available slots.');
            }

            
            UserSlot::create([
                'user_id'    => $user->id,
                'torrent_id' => $torrent->id,
                'free'       => $slotType === 'free' ? 1 : 0,
                'double'     => $slotType === 'double' ? 1 : 0,
                'expires_at' => now()->addDays(28),
            ]);

           
            $user->decrement('slots');

            // 🔹 Clear slot cache used by announce
        Redis::del("user_slot:{$user->id}:{$torrent->id}");
        });
    }

    // protected function trackerUrls(User $user): array
    // {
    //     // Add the announce-list for multiple trackers (extendable)
    //     return [
    //         // Primary tracker
    //        // [route('announce', ['passkey' => $user->passkey], false)],

    //         // Secondary tracker
    //         [config('app.seedbox_url') . "/announce/{$user->passkey}"],
    //         // Uncomment to add additional trackers
    //         // [config('app.site_url') . "/announce/{$user->passkey}"],
    //     ];
    // }
    protected function trackerUrls(User $user): array
{
    return [
        [
            'https://tracker.fileiplay.org/announce/' . $user->passkey
        ],
    ];
}

    protected function prepareTorrentResponse(Torrent $torrent, User $user)
    {
        // Get the path of the torrent file
        $path = public_path('files/torrents/' . $torrent->file_name);

        // Check if the file exists
        if (!file_exists($path)) {
            return response('Torrent file not found', 404)
                ->header('Content-Type', 'text/plain');
        }

        // Decode the torrent file
        $dict = Bencode::bdecode(file_get_contents($path));

        // Modify the announce URL and add a comment
        $dict['announce'] = config('app.seedbox_url') . "/announce/{$user->passkey}";
        $dict['comment']  = 'Using this torrent binds you to ' . config('app.name') . ' Confidentiality Agreement';
        $dict['created_by']  = config('app.name') . ' Upload Service';

        // Add announce-list
        $dict['announce-list'] = $this->trackerUrls($user);

        // Re-encode the torrent file
        $fileToDownload = Bencode::bencode($dict);
        

        // Generate a custom filename
        $prefix   = 'LF_[';
        //$fileName = $prefix . Str::slug($torrent->name, '_') . '.torrent';
         $fileName = $prefix . preg_replace('/[^a-zA-Z0-9-_]/', '_', $torrent->name) . '].torrent';

        // Return the torrent file as a download
        return response()->streamDownload(
            fn () => print($fileToDownload),
            $fileName,
            [
                'Content-Type'        => 'application/x-bittorrent',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Content-Length'      => strlen($fileToDownload),
            ]
        );
    }

public function handleUploadFromSeedbox(string $filePath, User $user)
{
    $request = new Request();
    $request->files->set('torrent', new \Illuminate\Http\UploadedFile(
        $filePath,
        basename($filePath),
        'application/x-bittorrent',
        null,
        true
    ));

    // Call your existing store method
    $tmdbService = app(\App\Services\TMDBService::class);
    app(\App\Http\Controllers\TorrentController::class)->store($request, $tmdbService);
}

}
