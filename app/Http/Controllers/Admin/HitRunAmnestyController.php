<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HitRun\HitRunAmnestyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserClass;

class HitRunAmnestyController extends Controller
{
    /**
     * Show a live preview of what the amnesty would change.
     */
    public function index()
    {
        // Only Admin and above can manage hit & runs.
        if (Auth::user()->user_class < UserClass::ADMIN) {
            abort(403, 'Unauthorized action.');
        }

        $stats = (new HitRunAmnestyService)->preview();

        return view('admin.hitrun_amnesty.index', compact('stats'));
    }

    /**
     * Apply the amnesty after explicit confirmation.
     */
    public function run(Request $request)
    {
        if (Auth::user()->user_class < UserClass::ADMIN) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'confirm' => 'required|accepted',
        ], [
            'confirm.accepted' => 'You must tick the confirmation box to run the amnesty.',
        ]);

        $stats = (new HitRunAmnestyService)->apply(notify: true);

        return redirect()
            ->route('admin.hitrun_amnesty.index')
            ->with('success', sprintf(
                'Amnesty applied: %s hit & runs cleared for %s user(s), %s bytes of upload credited (1:1 ratio), %s download privilege(s) restored, %s warning(s) cleared.',
                number_format($stats['records']),
                number_format($stats['users']),
                number_format($stats['total_upload_credited']),
                number_format($stats['downloads_restored']),
                number_format($stats['warnings_cleared'])
            ));
    }
}
