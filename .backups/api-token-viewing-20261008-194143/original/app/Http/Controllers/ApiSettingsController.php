<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Torrent\UploadPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class ApiSettingsController extends Controller
{
    private function scopes(Request $request): array
    {
        abort_unless(app(UploadPermission::class)->active($request->user()), 403);

        return app(UploadPermission::class)->canUpload($request->user())
            ? ['torrents:read', 'torrents:upload'] : ['torrents:read'];
    }

    public function index(Request $request)
    {
        return $this->page($request);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'scopes' => ['required', 'array', 'min:1', 'max:2'],
            'scopes.*' => ['required', 'string', 'distinct', Rule::in($this->scopes($request))],
            'expires_days' => ['required', Rule::in([30, 90, 365])],
        ]);
        // A user-row lock makes the token limit safe for simultaneous requests.
        $token = DB::transaction(function () use ($request, $data) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            if ($request->user()->tokens()->count() >= 20) {
                throw ValidationException::withMessages(['name' => 'Revoke an existing token before creating another.']);
            }

            return $request->user()->createToken(trim($data['name']), $data['scopes'], now()->addDays((int) $data['expires_days']));
        });

        // Render immediately: the plaintext is never saved in a session or database.
        return $this->page($request, $token->plainTextToken);
    }

    public function destroy(Request $request, int $token)
    {
        $this->scopes($request);
        $request->user()->tokens()->findOrFail($token)->delete();

        return redirect()->route('profile.api.index')->with('success', 'API token revoked.');
    }

    public function destroyAll(Request $request)
    {
        $this->scopes($request);
        $request->user()->tokens()->delete();

        return redirect()->route('profile.api.index')->with('success', 'All personal API tokens revoked.');
    }

    public function documentation(Request $request)
    {
        $this->scopes($request);

        return response()->view('profile.api.documentation', ['documentation' => (new GithubFlavoredMarkdownConverter(['html_input' => 'strip', 'allow_unsafe_links' => false]))->convert(file_get_contents(base_path('docs/upload-api.md')))->getContent()])->header('Cache-Control', 'private, no-store');
    }

    private function page(Request $request, ?string $newToken = null)
    {
        return response()->view('profile.api.index', [
            'user' => $request->user(), 'scopes' => $this->scopes($request),
            'tokens' => $request->user()->tokens()->latest()->get(['id', 'name', 'abilities', 'created_at', 'last_used_at', 'expires_at']),
            'newToken' => $newToken,
        ])->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer');
    }
}
