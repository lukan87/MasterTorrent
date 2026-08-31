<?php

namespace App\Http\Controllers;

use App\Models\HappyHour;
use App\Models\Shoutbox;
use Illuminate\Http\Request;


class HappyHourController extends Controller
{
    public function index()
{
    // Get all happy hours, latest first
$happyHours = HappyHour::with('user')
    ->orderByDesc('start_at')
    ->paginate(10)
    ->withQueryString();

    // Get current automatic theme for today
    $day = now()->dayOfWeek;
    $dayThemes = config('happyhour.day_themes', []);

    $theme = $dayThemes[$day] ?? [
        'name' => 'Classic Happy Hour',
        'upload_multiplier' => 3,
        'duration_hours' => 2,
        'free_download' => true,
    ];

    $automatic = HappyHour::where('automatic', true)
    ->where('active', true)
    ->exists();


    return view('admin.happyhour.index', compact('happyHours', 'automatic', 'theme'));
}


    public function create()
{
    $automatic = false; // default off
    $day = now()->dayOfWeek;

    // All day themes for select dropdown
    $dayThemes = config('happyhour.day_themes');

    // Today's theme as default
    $theme = $dayThemes[$day] ?? [
        'name' => 'Classic Happy Hour',
        'upload_multiplier' => 3,
        'duration_hours' => 2,
        'free_download' => true,
    ];

    return view('admin.happyhour.create', compact('automatic', 'theme', 'dayThemes'));
}


   public function store(Request $request)
{
    $data = $request->validate([
        'theme' => 'required|string',
        'custom_theme_name' => 'nullable|string|max:255',
        'upload_multiplier' => 'required|integer|min:1|max:10',
        'free_download' => 'nullable', // checkbox can be missing if unchecked
        'start_at' => 'required|date',
        'end_at' => 'required|date|after:start_at',
    ]);

    // Determine final theme name
    if ($data['theme'] === 'custom' && !empty($data['custom_theme_name'])) {
        $data['theme'] = $data['custom_theme_name'];
    }

    // Create the Happy Hour
    $happyHour = HappyHour::create([
        'theme' => $data['theme'],
        'upload_multiplier' => $data['upload_multiplier'],
        'free_download' => isset($data['free_download']),
        'start_at' => $data['start_at'],
        'end_at' => $data['end_at'],
        'automatic' => false,
        'active' => true,
        'activated_by' => auth()->id(),
    ]);

    // Post a single message in Shoutbox
    try {
        Shoutbox::create([
            'user_id' => auth()->id(),
            'message' => "🎉 Happy Hour <strong>{$happyHour->theme}</strong> has started! {$happyHour->upload_multiplier}x Uploads"
                        . ($happyHour->free_download ? " & Free Downloads!" : ""),
            'parent_id' => null,
        ]);
    } catch (\Throwable $e) {
        \Log::error("Failed to post Happy Hour to Shoutbox: " . $e->getMessage());
    }

    return redirect()->route('happyhour.index')
        ->with('success', 'Happy Hour started: ' . $happyHour->theme);
}



 public function toggleAutomatic()
{
    $day = now()->dayOfWeek;
    $dayThemes = config('happyhour.day_themes', []);

    $theme = $dayThemes[$day] ?? [
        'name' => 'Classic Happy Hour',
        'upload_multiplier' => config('happyhour.default_upload_multiplier'),
        'duration_hours' => config('happyhour.default_duration_hours'),
        'free_download' => config('happyhour.default_free_download'),
    ];

    $automatic = HappyHour::where('automatic', true)->first();
    $triggeredBy = auth()->user()->name;

    if ($automatic) {

        if ($automatic->active) {

            // Disable
            $automatic->update([
                'active' => false,
                'end_at' => now(),
            ]);

            $enabled = false;

            try {
                Shoutbox::create([
                    'user_id' => 2,
                    'message' => "⛔ Automatic Happy Hour <strong>{$automatic->theme}</strong> has been disabled by <strong>{$triggeredBy}</strong>.",
                    'parent_id' => null,
                ]);
            } catch (\Throwable $e) {
                \Log::error("Shoutbox disable failed: " . $e->getMessage());
            }

        } else {

            // Re-enable with TODAY'S THEME
            $automatic->update([
                'theme' => $theme['name'],
                'upload_multiplier' => $theme['upload_multiplier'],
                'free_download' => $theme['free_download'],
                'active' => true,
                'start_at' => now(),
                'end_at' => now()->addHours($theme['duration_hours']),
                'activated_by' => auth()->id(),
            ]);

            $enabled = true;

            try {
                Shoutbox::create([
                    'user_id' => 2,
                    'message' => "🎉 Automatic Happy Hour <strong>{$theme['name']}</strong> was started by <strong>{$triggeredBy}</strong>! "
                        . "{$theme['upload_multiplier']}x Upload"
                        . ($theme['free_download'] ? " & Free Downloads!" : ""),
                    'parent_id' => null,
                ]);
            } catch (\Throwable $e) {
                \Log::error("Shoutbox enable failed: " . $e->getMessage());
            }
        }

    } else {

        // Create automatic with TODAY'S THEME
        $automatic = HappyHour::create([
            'theme' => $theme['name'],
            'upload_multiplier' => $theme['upload_multiplier'],
            'free_download' => $theme['free_download'],
            'automatic' => true,
            'active' => true,
            'start_at' => now(),
            'end_at' => now()->addHours($theme['duration_hours']),
            'activated_by' => auth()->id(),
        ]);

        $enabled = true;

        try {
            Shoutbox::create([
                'user_id' => 2,
                'message' => "🎉 Automatic Happy Hour <strong>{$theme['name']}</strong> was started by <strong>{$triggeredBy}</strong>! "
                    . "{$theme['upload_multiplier']}x Upload"
                    . ($theme['free_download'] ? " & Free Downloads!" : ""),
                'parent_id' => null,
            ]);
        } catch (\Throwable $e) {
            \Log::error("Shoutbox create failed: " . $e->getMessage());
        }
    }

    return redirect()
        ->route('happyhour.index')
        ->with('success', 'Automatic Happy Hour ' . ($enabled ? 'enabled' : 'disabled'));
}





public function stop(HappyHour $happyHour)
{
    $happyHour->update([
        'active' => false,
        'end_at' => now(),
    ]);

    return back()->with('success', 'Happy Hour stopped.');
}

public function destroy(HappyHour $happyHour)
{
    if (HappyHour::where('automatic', true)->where('active', true)->exists()) {
        return back()->with('error', 'Cannot delete while Automatic mode is enabled.');
    }

    $happyHour->delete();

    return back()->with('success', 'Happy Hour deleted successfully.');
}


}
