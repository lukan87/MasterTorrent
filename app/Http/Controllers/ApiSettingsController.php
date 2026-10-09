<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Torrent\UploadPermission;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
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

            $token = $request->user()->createToken(trim($data['name']), $data['scopes'], now()->addDays((int) $data['expires_days']));
            DB::table('api_token_secrets')->insert([
                'personal_access_token_id' => $token->accessToken->id,
                'encrypted_token' => Crypt::encryptString($token->plainTextToken),
            ]);

            return $token;
        });

        // Plaintext stays out of sessions and storage; the separate reveal copy is encrypted.
        return $this->page($request, $token->plainTextToken);
    }

    public function reveal(Request $request, int $token)
    {
        $this->scopes($request);
        $ownedToken = $request->user()->tokens()->findOrFail($token);
        $request->validate(['password' => ['required', 'string', 'max:1024', 'current_password:web']]);
        if ($ownedToken->expires_at && $ownedToken->expires_at->isPast()) {
            throw ValidationException::withMessages(['token' => 'This token has expired. Generate a new one.']);
        }
        $encrypted = DB::table('api_token_secrets')->where('personal_access_token_id', $ownedToken->id)->value('encrypted_token');
        if (! $encrypted) {
            throw ValidationException::withMessages(['token' => 'This older token cannot be recovered. Revoke it and generate a new one.']);
        }
        try {
            $plaintext = Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            throw ValidationException::withMessages(['token' => 'This token cannot be revealed. Revoke it and generate a new one.']);
        }
        $parts = explode('|', $plaintext, 2);
        if (count($parts) !== 2 || $parts[0] !== (string) $ownedToken->id || ! hash_equals($ownedToken->token, hash('sha256', $parts[1]))) {
            throw ValidationException::withMessages(['token' => 'This token cannot be revealed. Revoke it and generate a new one.']);
        }

        return $this->page($request, $plaintext, true);
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

    private function page(Request $request, ?string $newToken = null, bool $revealedToken = false)
    {
        return response()->view('profile.api.index', [
            'user' => $request->user(), 'scopes' => $this->scopes($request),
            'tokens' => $request->user()->tokens()->latest()
                ->select(['id', 'name', 'abilities', 'created_at', 'last_used_at', 'expires_at'])
                ->selectSub(DB::table('api_token_secrets')->selectRaw('1')->whereColumn('personal_access_token_id', 'personal_access_tokens.id')->limit(1), 'can_reveal')->get(),
            'newToken' => $newToken, 'revealedToken' => $revealedToken,
        ])->header('Cache-Control', 'private, no-store')->header('Referrer-Policy', 'no-referrer');
    }
}
