<?php

namespace App\Http\Controllers;

use App\Models\HappyHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HappyHourController extends Controller
{
    public function index()
{
    // Get all happy hours, latest first
    $happyHours = \App\Models\HappyHour::orderByDesc('start_at')->get();

    // Get current automatic theme for today
    $day = now()->dayOfWeek;
    $dayThemes = config('happyhour.day_themes', []);

    $theme = $dayThemes[$day] ?? [
        'name' => 'Classic Happy Hour',
        'upload_multiplier' => 3,
        'duration_hours' => 2,
        'free_download' => true,
    ];

    $automatic = \App\Models\HappyHour::where('automatic', true)->where('active', true)->exists();

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
        \App\Models\Shoutbox::create([
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
        $latest = HappyHour::latest()->first();

        if (!$latest) {
            $latest = HappyHour::create([
                'automatic' => true,
                'active' => false,
                'upload_multiplier' => 3,
                'free_download' => true,
                'theme' => 'Classic Happy Hour',
            ]);
        } else {
            $latest->update(['automatic' => !$latest->automatic]);
        }

        return back()->with('success', 'Automatic Happy Hour ' . ($latest->automatic ? 'enabled' : 'disabled') . '.');
    }

    public function stop(HappyHour $happyHour)
    {
        $happyHour->update(['active' => false, 'end_at' => now()]);
        return back()->with('success', 'Happy Hour stopped.');
    }
}
