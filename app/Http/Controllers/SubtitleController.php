<?php
namespace App\Http\Controllers;

use App\Models\Subtitle;
use App\Models\Torrent;
use App\Models\UserClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SubtitleController extends Controller
{
   public function store(Request $request, Torrent $torrent)
{
    $request->validate([
        'subtitle' => [
            'required',
            'file',
            'mimes:txt,srt,sub,ass,ssa,zip,rar',
            'max:51200' // 50MB
        ],
        'language' => 'nullable|string|max:10',
    ]);

    $file = $request->file('subtitle');
    $originalName = $file->getClientOriginalName();

    // Check for existing subtitle with the same name for this torrent
    $existing = Subtitle::where('torrent_id', $torrent->id)
                        ->where('original_name', $originalName)
                        ->first();

    if ($existing) {
        return back()->with([
            'error' => 'A subtitle with this name has already been uploaded for this torrent.'
        ])->withInput();
    }

    $path = $file->store(
        'subtitles/' . $torrent->id,
        'local'
    );

    Subtitle::create([
        'torrent_id'    => $torrent->id,
        'language'      => $request->language,
        'original_name' => $originalName,
        'file_path'     => $path,
        'extension'     => $file->getClientOriginalExtension(),
        'size'          => $file->getSize(),
        'uploaded_by'   => auth()->id(),
    ]);

    return back()->with('success', 'Subtitle uploaded successfully');
}


public function download(Subtitle $subtitle)
{
    // Optional: permission checks
    // if (!auth()->user()->can('download', $subtitle)) abort(403);

    if (!auth()->check()) {
        abort(403);
    }

    if (!Storage::exists($subtitle->file_path)) {
        abort(404, 'Subtitle file not found.');
    }

    return Storage::download(
        $subtitle->file_path,
        $subtitle->original_name
    );
}

public function destroy(Subtitle $subtitle)
{
    $user = auth()->user();

    // Permissions:
    // uploader OR moderator+
    if (
        $user->id !== $subtitle->uploaded_by &&
        $user->user_class < UserClass::MODERATOR
    ) {
        abort(403, 'You are not allowed to delete this subtitle.');
    }

    // Delete file from storage
    if (Storage::exists($subtitle->file_path)) {
        Storage::delete($subtitle->file_path);
    }

    // Delete DB record
    $subtitle->delete();

    return back()->with('success', 'Subtitle deleted successfully.');
}


}
