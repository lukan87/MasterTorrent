<?php

namespace App\Http\Controllers;

use App\Services\ActorService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use RuntimeException;

class ActorsController extends Controller
{
    public function show(int $actor, ActorService $actors)
    {
        abort_if($actor < 1 || $actor > 2147483647, 404);

        try {
            return view('actors.show', $actors->find($actor));
        } catch (RequestException $e) {
            if ($e->response->status() === 404) {
                return response()->view('actors.unavailable', ['missing' => true], 404);
            }
        } catch (ConnectionException|RuntimeException $e) {
            // Do not expose provider URLs or API credentials in errors.
        }

        return response()->view('actors.unavailable', ['missing' => false], 503, ['Retry-After' => '60']);
    }
}
