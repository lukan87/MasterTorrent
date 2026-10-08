<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Torrent;
use App\Services\TorrentDownloadService;
use App\Services\TorznabService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TorznabController extends Controller
{
    public function setup(Request $request, TorznabService $service)
    {
        abort_if($request->user()->enabled === 'no', 403, 'Your account is disabled.');

        return response()->view('torznab.setup', ['apiKey' => $service->key($request->user())])
            ->header('Cache-Control', 'private, no-store');
    }

    public function api(Request $request, TorznabService $service)
    {
        $token = $request->query('apikey', '');
        $user = is_string($token) ? $service->authenticate($token) : null;
        if (! $user) {
            return $this->error(100, 'Invalid API key or inactive account.', 401);
        }
        $validator = Validator::make($request->query(), [
            't' => 'required|in:caps,search,movie,tvsearch,get',
            'q' => 'sometimes|string|max:255',
            'cat' => ['sometimes', 'string', 'max:255', 'regex:/^\d+(,\d+)*$/'],
            'imdbid' => ['sometimes', 'string', 'regex:/^(tt)?\d{1,12}$/i'],
            'tmdbid' => 'sometimes|integer|min:1', 'tvdbid' => 'sometimes|integer|min:1',
            'year' => 'sometimes|integer|min:1900|max:2100',
            'season' => 'sometimes|integer|min:0|max:2100',
            'ep' => ['sometimes', 'string', 'regex:/^(\d{1,3}|\d{2}\/\d{2})$/'],
            'offset' => 'sometimes|integer|min:0|max:100000', 'limit' => 'sometimes|integer|min:1|max:100',
            'id' => 'required_if:t,get|integer|min:1',
        ]);
        if ($validator->fails()) {
            return $this->error(200, 'Invalid or unsupported search parameters.', 400);
        }
        $params = $validator->validated();
        if ($params['t'] === 'caps') {
            return $this->xml(view('torznab.caps', ['categories' => TorznabService::CATEGORIES])->render());
        }
        if ($params['t'] === 'get') {
            if ($user->enabled === 'no' || $user->downloadpos === 'no' || $user->hit_and_run_count > 20) {
                return $this->error(102, 'Download permission is restricted.', 403);
            }
            $torrent = Torrent::whereNotIn('category_id', Category::ADULT_IDS)->find($params['id']);
            if (! $torrent) {
                return $this->error(300, 'Torrent not found.', 404);
            }
            // Use the standard downloader without consuming free/double slots from API parameters.
            $download = Request::create('/torznab/download', 'GET');
            $download->setUserResolver(fn () => $user);
            try {
                $response = app(TorrentDownloadService::class)->handleDownload($download, $torrent->id, $torrent->slug);
                if ($response->getStatusCode() >= 400) {
                    return $this->error(300, 'Torrent file is unavailable.', $response->getStatusCode());
                }
                $response->headers->set('Cache-Control', 'private, no-store');

                return $response;
            } catch (\Throwable $exception) {
                return $this->error(300, 'Torrent download is unavailable.', 503);
            }
        }
        try {
            $data = $service->search($params);

            return $this->xml(view('torznab.results', $data + ['apiKey' => $token])->render());
        } catch (\InvalidArgumentException $exception) {
            return $this->error(200, 'Invalid episode search.', 400);
        } catch (\Throwable $exception) {
            // Provider exceptions can contain credentials; never echo or log their message.
            return $this->error(900, 'Search is temporarily unavailable.', 503);
        }
    }

    private function xml(string $body, int $status = 200)
    {
        return response('<?xml version="1.0" encoding="UTF-8"?>'."\n".$body, $status)
            ->header('Content-Type', 'application/xml; charset=UTF-8')->header('Cache-Control', 'private, no-store');
    }

    private function error(int $code, string $description, int $status)
    {
        return $this->xml('<error code="'.$code.'" description="'.htmlspecialchars($description, ENT_QUOTES | ENT_XML1, 'UTF-8').'" />', $status);
    }
}
