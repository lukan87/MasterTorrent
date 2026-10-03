<?php

namespace App\Http\Controllers;

use App\Models\HappyHour;
use App\Services\HappyHourService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HappyHourController extends Controller
{
    public function __construct(private HappyHourService $service) {}

    public function index()
    {
        return view('admin.happyhour.index', [
            'happyHours' => HappyHour::with('user')->orderByDesc('start_at')->orderByDesc('id')->paginate(15),
            'automatic' => $this->service->automaticEnabled(),
            'theme' => $this->service->theme(),
            'current' => HappyHour::current()->first(),
            'upcoming' => HappyHour::where('active', true)->where('start_at', '>', now())->orderBy('start_at')->first(),
        ]);
    }

    public function create()
    {
        return view('admin.happyhour.create', ['theme' => $this->service->theme(), 'dayThemes' => config('happyhour.day_themes', [])]);
    }

    public function store(Request $request)
    {
        $themes = array_column(config('happyhour.day_themes', []), 'name');
        $data = $request->validate([
            'theme' => ['required', Rule::in([...$themes, 'custom'])],
            'custom_theme_name' => 'required_if:theme,custom|nullable|string|max:100',
            'upload_multiplier' => 'required|integer|min:1|max:10',
            'free_download' => 'required|boolean',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at|after:now',
        ]);
        $data['theme'] = $data['theme'] === 'custom' ? $data['custom_theme_name'] : $data['theme'];
        unset($data['custom_theme_name']);
        $event = $this->service->create($data + ['activated_by' => $request->user()->id]);
        $this->service->announce($event, $event->isActive() ? 'has started' : 'is scheduled');

        return redirect()->route('happyhour.index')->with('success', 'Happy Hour '.$event->status.': '.$event->theme);
    }

    public function toggleAutomatic(Request $request)
    {
        $data = $request->validate(['enabled' => 'required|boolean']);
        $this->service->locked(fn () => DB::table('happy_hour_settings')->where('id', 1)
            ->update(['automatic_enabled' => (bool) $data['enabled']]));

        return back()->with('success', $data['enabled']
            ? 'Automatic events enabled. The scheduler will check hourly after 07:00.'
            : 'Automatic events disabled. Existing events keep their scheduled times.');
    }

    public function stop(HappyHour $happyHour)
    {
        $this->service->locked(function () use ($happyHour) {
            $happyHour->refresh();
            $data = ['active' => false];
            if ($happyHour->isActive()) {
                $data['end_at'] = now();
            }
            $happyHour->update($data);
        });

        return back()->with('success', 'Happy Hour stopped or cancelled.');
    }

    public function destroy(HappyHour $happyHour)
    {
        return $this->service->locked(function () use ($happyHour) {
            $happyHour->refresh();
            if ($happyHour->isActive() || $happyHour->status === 'Scheduled') {
                return back()->with('error', 'Stop or cancel this event before deleting it.');
            }
            if ($happyHour->automatic && $happyHour->start_at?->isToday()) {
                return back()->with('error', 'Keep today’s automatic event in history to prevent another automatic start today. You can delete it tomorrow.');
            }
            $happyHour->delete();

            return back()->with('success', 'Happy Hour deleted.');
        });
    }
}
