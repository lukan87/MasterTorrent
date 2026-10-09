<?php

use App\Http\Controllers\Api\V1\TorrentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->middleware(['upload-api-size', 'throttle:upload-api-auth', 'auth:sanctum'])->group(function () {
    Route::post('torrents', [TorrentController::class, 'store'])
        ->middleware(['upload-api-access:torrents:upload', 'throttle:upload-api-write'])->name('torrents.store');
    Route::middleware(['upload-api-access:torrents:read', 'throttle:upload-api-read'])->group(function () {
        Route::get('categories', [TorrentController::class, 'categories'])->name('categories.index');
        Route::get('torrents/{torrent}', [TorrentController::class, 'show'])->whereNumber('torrent')->name('torrents.show');
    });
});
