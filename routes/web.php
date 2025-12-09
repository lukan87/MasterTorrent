<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TorrentController;
use App\Http\Controllers\AnnounceController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\ShoutboxController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\ResetPassword\ResetPasswordController;
use App\Http\Controllers\TorrentRequestController;
use App\Http\Controllers\Admin\SystemInfoController;
use App\Http\Controllers\RssFeedController;
use App\Http\Controllers\TorrentHistoryController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\HitAndRunController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UploadAppController;
use App\Http\Controllers\HappyHourController;
use App\Helpers\EmojiHelper;


use App\Http\Controllers\OverforumController;

use App\Http\Controllers\SeedboxController;

Route::delete('/profile/{id}/{name}/delete', [ProfileController::class, 'deleteAccount'])
    ->name('profile.delete')
    ->middleware('auth');

Route::get('/seedboxes/{seedbox}/test-rpc/{hash}', [SeedboxController::class, 'testRpc'])->middleware('auth');

Route::get('/seedboxes/{seedbox}/torrent/{hash}/download', [SeedboxController::class, 'downloadTorrent'])->name('seedboxes.downloadTorrent');

// routes/web.php
Route::get('seedboxes/{seedbox}/download-torrent/{hash}', [SeedboxController::class, 'downloadTorrentFile'])
    ->name('seedboxes.downloadTorrent');

    Route::get('seedboxes/{seedbox}/torrent/{hash}/download-rebuilt', [SeedboxController::class, 'downloadRebuiltTorrent'])
    ->name('seedboxes.downloadRebuiltTorrent');





//Happy Hour
Route::prefix('admin/happyhour')->name('happyhour.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [HappyHourController::class, 'index'])->name('index');
    Route::get('/create', [HappyHourController::class, 'create'])->name('create');
    Route::post('/store', [HappyHourController::class, 'store'])->name('store');
    Route::post('/toggle-automatic', [HappyHourController::class, 'toggleAutomatic'])->name('toggleAutomatic');
    Route::patch('/stop/{happyHour}', [HappyHourController::class, 'stop'])->name('stop');
});
//Happy Hour

Route::middleware('auth')->group(function () {
    Route::resource('seedboxes', SeedboxController::class);
    Route::get('seedboxes/{seedbox}/test', [SeedboxController::class, 'testConnection'])->name('seedboxes.test');
    Route::get('seedboxes/{seedbox}/torrents', [SeedboxController::class, 'showTorrents'])->name('seedboxes.torrents');
    Route::post('/seedboxes/{seedbox}/add-torrent', [SeedboxController::class, 'addTorrent'])->name('seedboxes.addTorrent');
    Route::post('/seedboxes/{seedbox}/start/{hash}', [SeedboxController::class, 'start'])->name('seedboxes.start');
Route::post('/seedboxes/{seedbox}/pause/{hash}', [SeedboxController::class, 'pause'])->name('seedboxes.pause');
Route::delete('/seedboxes/{seedbox}/delete/{hash}', [SeedboxController::class, 'delete'])->name('seedboxes.delete');
Route::get('/seedboxes/{seedbox}/test', [SeedboxController::class, 'testConnection'])->name('seedboxes.test');
Route::post('/seedboxes/{seedbox}/add-url', [SeedboxController::class, 'addTorrentUrl'])->name('seedboxes.addTorrentUrl');
});

Route::get('seedboxes/{seedbox}/torrent/{hash}/trackers', [SeedboxController::class, 'getTorrentTrackers']);



Route::post('/torrents/{torrent}/send-to-seedbox', [TorrentController::class, 'sendToSeedbox'])
    ->name('torrents.sendToSeedbox')
    ->middleware('auth');

  Route::post('seedboxes/{seedbox}/import/{hash}', [SeedboxController::class, 'importTorrent'])
    ->name('seedboxes.import')
      ->middleware('auth');

Route::get('seedboxes/{seedbox}/download/{hash}', [SeedboxController::class, 'downloadTorrent'])
    ->name('seedboxes.download');







Route::get('/overforums', [OverforumController::class, 'index'])->name('overforums.index')->middleware('auth');
Route::get('/overforums/create', [OverforumController::class, 'create'])->name('overforums.create')->middleware('auth');
Route::post('/overforums', [OverforumController::class, 'store'])->name('overforums.store')->middleware('auth');
Route::get('/overforums/{id}', [OverforumController::class, 'show'])->name('overforums.show')->middleware('auth');
Route::delete('/overforums/{id}', [OverforumController::class, 'destroy'])->name('overforums.destroy')->middleware('auth');
Route::get('overforums/{overforum}/edit', [OverforumController::class, 'edit'])->name('overforums.edit')->middleware('auth');
Route::put('overforums/{overforum}', [OverforumController::class, 'update'])->name('overforums.update')->middleware('auth');



use App\Http\Controllers\ForumController;

Route::get('/overforums/{overforumId}/forums', [ForumController::class, 'index'])->name('forums.index')->middleware('auth');
Route::get('/overforums/{overforumId}/forums/create', [ForumController::class, 'create'])->name('forums.create')->middleware('auth');
Route::post('/overforums/{overforumId}/forums', [ForumController::class, 'store'])->name('forums.store')->middleware('auth');
Route::get('/overforums/{overforumId}/forums/{forumId}', [ForumController::class, 'show'])->name('forums.show')->middleware('auth');


use App\Http\Controllers\TopicController;

Route::get('/forums/{forumId}/topics', [TopicController::class, 'index'])->name('topics.index')->middleware('auth');
Route::get('/forums/{forumId}/topics/create', [TopicController::class, 'create'])->name('topics.create')->middleware('auth');
Route::post('/forums/{forumId}/topics', [TopicController::class, 'store'])->name('topics.store')->middleware('auth');
Route::get('/forums/{forumId}/topics/{topicId}', [TopicController::class, 'show'])->name('topics.show')->middleware('auth');


use App\Http\Controllers\PostController;

// Store a new post
Route::post('/topics/{topicId}/posts', [PostController::class, 'store'])->name('posts.store')->middleware('auth');
Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit')->middleware('auth');
Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update')->middleware('auth');
Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy')->middleware('auth');
Route::post('/posts/{post}/reply', [PostController::class, 'reply'])->name('posts.reply')->middleware('auth');
Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy')->middleware('auth');



use App\Http\Controllers\SnatchController;

Route::prefix('snatch')->group(function () {
    Route::get('/snatchlist/{userId?}', [SnatchController::class, 'snatchlist'])->name('snatch.snatchlist')->middleware('auth');
    Route::get('/seeding/{userId?}', [SnatchController::class, 'seeding'])->name('snatch.seeding')->middleware('auth');
    Route::get('/leeching/{userId?}', [SnatchController::class, 'leeching'])->name('snatch.leeching')->middleware('auth');
    Route::get('/hit-and-run/{userId?}', [SnatchController::class, 'hitAndRun'])->name('snatch.hitAndRun')->middleware('auth');
    Route::get('/need-to-seed/{userId?}', [SnatchController::class, 'needToSeed'])->name('snatch.needToSeed')->middleware('auth');
    
     Route::delete('/delete-need-to-seed/{userId}/{torrentId}', [SnatchController::class, 'deleteNeedToSeed'])
     ->name('snatch.deleteNeedToSeed')
     ->middleware('auth');
     
 Route::delete('/delete-hnr/{userId}/{torrentId}', [SnatchController::class, 'deleteHNR'])
     ->name('snatch.deleteHNR')
     ->middleware('auth');
});




Route::get('/get-emoji/{emojiCode}', function ($emojiCode) {
    return response()->json(['emoji' => emoji($emojiCode)]);
});


Route::resource('uploadapps', UploadAppController::class)->middleware('auth');
Route::put('/uploadapps/{id}/update-status', [UploadAppController::class, 'updateStatus'])->name('uploadapps.updateStatus')->middleware('auth');






Route::get('/hitandrun', [HitAndRunController::class, 'index'])->name('hitandrun.index')->middleware('auth');
// View another user's hit and run status
Route::get('/hitandrun/{userId}', [HitAndRunController::class, 'showOtherUserHitAndRun'])->name('hitandrun.showOther')->middleware('auth');

Route::post('/bonus/buy-vip', [BonusController::class, 'buyVip'])->name('bonus.buyVip')->middleware('auth');

Route::post('/bonus/buy-seedtime', [BonusController::class, 'buySeedtime'])->name('bonus.buySeedtime')->middleware('auth');
Route::post('/remove-hnr', [BonusController::class, 'removeHNR'])->name('bonus.removeHNR')->middleware('auth');
Route::post('/buy-invites', [BonusController::class, 'buyInvites'])->name('buy.invites')->middleware('auth');
Route::post('/buy-slots', [BonusController::class, 'buySlots'])->name('buy.slots')->middleware('auth');
Route::post('/bonus/surprise', [BonusController::class, 'buySurprise'])->name('bonus.surprise')->middleware('auth');

Route::get('/staff', [StaffController::class, 'index'])->name('staff.index')->middleware('auth');




Auth::routes();
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('last_activity')->middleware('auth');
Route::get('/recover-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showRecoveryForm'])->name('password.recover');
Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'updatePassword'])->name('password.update');

Route::get('/test-ip', function () {
    return request()->ip();
});

// Custom password recovery routes
Route::get('/custom-password/recover', [ResetPasswordController::class, 'showRecoveryForm'])->name('custom.password.recover');
Route::post('/custom-password/reset', [ResetPasswordController::class, 'updatePassword'])->name('custom.password.update');



// Show user profile by ID and name
Route::get('/profile/{id}/{name?}', [ProfileController::class, 'show'])->name('profile.show')->middleware('auth');

// Edit user profile by ID and name
Route::get('/profile/{id}/{name}/edit', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');

// Update user profile by ID and name
Route::put('/profile/{id}/{name}', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');

// User's Torrents
Route::get('profile/{id}/{name}/torrents', [ProfileController::class, 'userTorrents'])->name('profile.torrents')->middleware('auth');

// Seeding Torrents
Route::get('/profile/{id}/{name}/seeding-torrents', [ProfileController::class, 'seedingTorrents'])->name('profile.seedingTorrents')->middleware('auth');

Route::get('/profile/{id}/{name}/download-history', [ProfileController::class, 'downloadHistory'])->name('profile.download-history')->middleware('auth');

//Slots
Route::get('/profile/{id}/{name}/tokens', [ProfileController::class, 'activeTokens'])
    ->name('profile.tokens');

Route::delete('/profile/torrents/{torrent}/delete', [\App\Http\Controllers\ProfileController::class, 'destroyTorrent'])
    ->name('profile.torrents.destroy')
    ->middleware('auth');





// Movies
Route::resource('movies', MovieController::class)
    ->middleware('auth')
    ->except(['show']); // prevent duplicate movies.show

// Additional custom routes
Route::post('/movies/search', [MovieController::class, 'search'])
    ->name('movies.search')
    ->middleware('auth');

Route::get('/movies/select/{tmdb_id}', [MovieController::class, 'selectMovie'])
    ->name('movies.select')
    ->middleware('auth');

Route::get('/movies/{id}/{slug?}', [MovieController::class, 'show'])
    ->name('movies.show')
    ->middleware('auth');

Route::post('/movies/bulk-select', [MovieController::class, 'bulkSelect'])
    ->name('movies.bulkSelect')
    ->middleware('auth');

Route::post('/movies/search-movie', [MovieController::class, 'searchMovie'])
    ->name('movies.search-movie')
    ->middleware('auth');




// Series
Route::resource('series', SeriesController::class)
    ->middleware('auth')
    ->except(['show']); // exclude show so your SEO-friendly route takes over

// Additional custom routes
Route::post('/series/search', [SeriesController::class, 'search'])
    ->name('series.search')
    ->middleware('auth');

Route::get('/series/select/{tmdb_id}', [SeriesController::class, 'selectSeries'])
    ->name('series.select')
    ->middleware('auth');

Route::get('/series/{id}/{slug?}', [SeriesController::class, 'show'])
    ->name('series.show')
    ->middleware('auth');

Route::post('/series/bulk-select', [SeriesController::class, 'bulkSelect'])
    ->name('series.bulkSelect')
    ->middleware('auth');

Route::post('/series/search-movie', [SeriesController::class, 'searchSeries'])
    ->name('series.search-series')
    ->middleware('auth');



// Collections
Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index')->middleware('auth');
Route::get('/collections/{id}', [CollectionController::class, 'show'])->name('collections.show')->middleware('auth');

//Comments

Route::post('/comments', [CommentController::class, 'store'])->middleware('auth')->name('comments.store')->middleware('auth');
Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->middleware('auth')->name('comments.destroy')->middleware('auth');
Route::put('/comments/{id}/update', [CommentController::class, 'update'])->name('comments.update')->middleware('auth');



// Admin System
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {

    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('admin.index');
    Route::name('admin.')->group(function () {
        // Users Management
            Route::group(['prefix' => 'users'], function () {
            Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
            Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
            Route::post('/store', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
            Route::get('/{name}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
            Route::put('/{id}/update', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
            Route::delete('/{id}/destroy', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
            Route::post('/admin/users/sendMassMessage', [App\Http\Controllers\Admin\UserController::class, 'sendMassMessage'])->name('users.sendMassMessage');
            Route::get('/users/comments', [App\Http\Controllers\Admin\UserController::class, 'comments'])->name('users.comments');


        });

        // Movies Management
        Route::group(['prefix' => 'movies'], function () {
            Route::get('/', [App\Http\Controllers\Admin\MovieController::class, 'index'])->name('movies.index');
            Route::get('/create', [App\Http\Controllers\Admin\MovieController::class, 'create'])->name('movies.create');
            Route::post('/store', [App\Http\Controllers\Admin\MovieController::class, 'store'])->name('movies.store');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\MovieController::class, 'edit'])->name('movies.edit');
            Route::put('/{id}/update', [App\Http\Controllers\Admin\MovieController::class, 'update'])->name('movies.update');
            Route::delete('/{id}/destroy', [App\Http\Controllers\Admin\MovieController::class, 'destroy'])->name('movies.destroy');
        });

        // Series Management
        Route::group(['prefix' => 'series'], function () {
            Route::get('/', [App\Http\Controllers\Admin\SeriesController::class, 'index'])->name('series.index');
            Route::get('/create', [App\Http\Controllers\Admin\SeriesController::class, 'create'])->name('series.create');
            Route::post('/store', [App\Http\Controllers\Admin\SeriesController::class, 'store'])->name('series.store');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\SeriesController::class, 'edit'])->name('series.edit');
            Route::put('/{id}/update', [App\Http\Controllers\Admin\SeriesController::class, 'update'])->name('series.update');
            Route::delete('/{id}/destroy', [App\Http\Controllers\Admin\SeriesController::class, 'destroy'])->name('series.destroy');
        });

        // Torrents Management
        Route::group(['prefix' => 'torrents'], function () {
            Route::get('/', [App\Http\Controllers\Admin\TorrentsController::class, 'index'])->name('torrents.index');
            Route::get('/{id}', [App\Http\Controllers\Admin\TorrentsController::class, 'show'])->name('torrents.show');
            Route::get('/create', [App\Http\Controllers\Admin\TorrentsController::class, 'create'])->name('torrents.create');
            Route::post('/store', [App\Http\Controllers\Admin\TorrentsController::class, 'store'])->name('torrents.store');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\TorrentsController::class, 'edit'])->name('torrents.edit');
            Route::put('/{id}/update', [App\Http\Controllers\Admin\TorrentsController::class, 'update'])->name('torrents.update');
            Route::delete('/{id}/destroy', [App\Http\Controllers\Admin\TorrentsController::class, 'destroy'])->name('torrents.destroy');
        });

         // System Info Routes
         Route::group(['prefix' => 'system-info'], function () {
            Route::get('/', [SystemInfoController::class, 'index'])->name('systemInfo.index');
            Route::post('/clear-cache', [SystemInfoController::class, 'clearCache'])->name('systemInfo.clearCache');
            Route::post('/clear-views', [SystemInfoController::class, 'clearViews'])->name('systemInfo.clearViews');
            Route::post('/clear-routes', [SystemInfoController::class, 'clearRoutes'])->name('systemInfo.clearRoutes');
            Route::post('/clear-config', [SystemInfoController::class, 'clearConfig'])->name('systemInfo.clearConfig');
            Route::get('/show-routes', [SystemInfoController::class, 'showRoutes'])->name('systemInfo.showRoutes');
            Route::post('/system/backup', [SystemInfoController::class, 'backup'])->name('systemInfo.backup');
        });
        });


    });




//Torrents
// Announce
Route::any('/announce/{passkey}', [AnnounceController::class, 'announce'])->name('announce');



// Group the routes under authentication middleware
Route::middleware('auth')->group(function () {
    Route::get('torrents', [TorrentController::class, 'index'])->name('torrents.index');        // Show all torrents
    Route::get('torrents/create', [TorrentController::class, 'create'])->name('torrents.create');// Show form to create a new torrent
    Route::post('torrents', [TorrentController::class, 'store'])->name('torrents.store');        // Store a new torrent
    // Route::get('torrents/{torrent}', [TorrentController::class, 'show'])->name('torrents.show');  // Show a specific torrent
    Route::get('torrents/{id}/{slug}/edit', [TorrentController::class, 'edit'])->name('torrents.edit'); // Show form to edit a torrent
    Route::put('torrents/{torrent}', [TorrentController::class, 'update'])->name('torrents.update'); // Update a specific torrent
    Route::delete('torrents/{torrent}', [TorrentController::class, 'destroy'])->name('torrents.destroy'); // Delete a specific torrent
    Route::get('/torrents/adult', [TorrentController::class, 'adult'])->name('torrents.adult');

});
Route::get('/torrents/check-imdb', [TorrentController::class, 'checkImdbUrl']);

Route::get('/torrents/{id}/{slug?}', [TorrentController::class, 'show'])->name('torrents.show')->middleware('auth');
Route::get('/torrents/download/{id}/{slug}', [TorrentController::class, 'download'])->name('torrents.download');
Route::get('/torrent/{torrent}/peers', [TorrentController::class, 'peers'])->name('torrent.peers')->middleware('auth');
Route::get('/torrents/{id}/{slug}/history', [TorrentHistoryController::class, 'index'])->name('torrent.history')->middleware('auth');

Route::post('torrents/{id}/thank', [TorrentController::class, 'thank'])->name('torrents.thank')->middleware('auth');
Route::post('/slots/renew/{slotId}', [TorrentController::class, 'renewSlot'])->name('slots.renew')->middleware('auth');
Route::post('/slots/remove/{slotId}', [TorrentController::class, 'removeSlot'])->name('slots.remove')->middleware('auth');
Route::post('/torrents/bump/{id}', [TorrentController::class, 'bump'])->name('torrents.bump')->middleware('auth');
Route::delete('/torrents/bulk-delete', [TorrentController::class, 'bulkDelete'])->name('torrents.bulkDelete');









//RSS//

Route::get('/rss', [RssFeedController::class, 'index'])->name('rss.index')->middleware('auth'); // Show the form
Route::get('/rss/feed', [RssFeedController::class, 'generateFeed'])->name('rss.feed'); // Generate the RSS feed
Route::get('/rss/download/{fileName}/{passkey}', [RssFeedController::class, 'downloadrss'])->name('rss.download');




//RSS//


//Bonus page//

// Route to show the shop
Route::get('/shop', [BonusController::class, 'showShop'])->name('shop')->middleware('auth');

// Route for purchasing upload space (POST method)
Route::post('/shop', [BonusController::class, 'buyUpload'])->name('shop.upload')->middleware('auth');



// Donate page beta //
Route::get('/donate', [DonationController::class, 'index'])->name('donate');

//Rules Page Beta
Route::get('/rules', function () {
    return view('rules');
})->middleware('auth')->name('rules');





// Shoutbox//

//Shoutbox



Route::get('/shoutbox', [ShoutboxController::class, 'index'])->name('shoutbox.index')->middleware('auth');
Route::post('/shoutbox', [ShoutboxController::class, 'store'])->name('shoutbox.store')->middleware('auth');
Route::get('/shoutbox/{id}/edit', [ShoutboxController::class, 'edit'])->name('shoutbox.edit')->middleware('auth');
Route::put('/shoutbox/{id}', [ShoutboxController::class, 'update'])->name('shoutbox.update')->middleware('auth');
Route::delete('/shoutbox/{id}', [ShoutboxController::class, 'destroy'])->name('shoutbox.destroy')->middleware('auth');
Route::get('/shoutbox/{id}/reply', [ShoutboxController::class, 'showReplyForm'])->name('shoutbox.showReplyForm')->middleware('auth');
Route::post('/shoutbox/{id}/reply', [ShoutboxController::class, 'reply'])->name('shoutbox.reply')->middleware('auth');



Route::middleware(['auth'])->group(function () {
    Route::get('messages/inbox', [MessageController::class, 'inbox'])->name('messages.inbox');
    Route::get('messages/outbox', [MessageController::class, 'outbox'])->name('messages.outbox');
    Route::get('messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::post('/messages/{message}/reply', [MessageController::class, 'storeReply'])->name('messages.storeReply');

});

//News//


Route::middleware('auth')->group(function () {
    // Display the list of news articles (index page)
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');

    // Display the form to create a new news article
    Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');

    // Store the newly created news article in the database
    Route::post('/news', [NewsController::class, 'store'])->name('news.store');

    Route::get('/news/{news}', [NewsController::class, 'show'])->name('news.show');

    // Display the form to edit an existing news article
    Route::get('/news/{news}/edit', [NewsController::class, 'edit'])->name('news.edit');

    // Update the news article in the database
    Route::put('/news/{news}', [NewsController::class, 'update'])->name('news.update');

    // Delete a news article from the database
    Route::delete('/news/{news}', [NewsController::class, 'destroy'])->name('news.destroy');
});

//POLS

// Route::resource('polls', PollController::class);
// Display a list of all polls
Route::get('polls', [PollController::class, 'index'])->name('polls.index')->middleware('auth');

// Show the form for creating a new poll
Route::get('polls/create', [PollController::class, 'create'])->name('polls.create')->middleware('auth');

// Store a newly created poll in storage
Route::post('polls', [PollController::class, 'store'])->name('polls.store')->middleware('auth');

// Display a specific poll
Route::get('polls/{poll}', [PollController::class, 'show'])->name('polls.show')->middleware('auth');

// Show the form for editing a specific poll
Route::get('polls/{poll}/edit', [PollController::class, 'edit'])->name('polls.edit')->middleware('auth');

// Update a specific poll in storage
Route::put('polls/{poll}', [PollController::class, 'update'])->name('polls.update')->middleware('auth');

// Delete a specific poll
Route::delete('polls/{poll}', [PollController::class, 'destroy'])->name('polls.destroy')->middleware('auth');


Route::post('/polls/{pollId}/vote', [PollController::class, 'vote'])->name('polls.vote')->middleware('auth');

//Requests//

Route::middleware('auth')->group(function () {
    Route::get('/requests', [TorrentRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [TorrentRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [TorrentRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{request}', [TorrentRequestController::class, 'show'])->name('requests.show');
    Route::get('/requests/{request}/edit', [TorrentRequestController::class, 'edit'])->name('requests.edit');
    Route::put('/requests/{request}', [TorrentRequestController::class, 'update'])->name('requests.update');
    Route::delete('/requests/{request}', [TorrentRequestController::class, 'destroy'])->name('requests.destroy');
    Route::post('/requests/{id}/fill', [TorrentRequestController::class, 'fillRequest'])->name('requests.fill');

});




use App\Http\Controllers\WarningController;

Route::middleware(['auth'])->group(function () {
    Route::get('/warnings', [WarningController::class, 'index'])->name('warnings.index');
    Route::get('/warnings/{id}/{username?}', [WarningController::class, 'show'])->name('warnings.show');
    Route::post('/warnings/deactivate/{id}', [WarningController::class, 'deactivate'])->name('warnings.deactivate');
    Route::post('/warnings/deactivate-all/{id}/{username?}', [WarningController::class, 'deactivateAllWarnings'])->name('warnings.deactivateAll');
    Route::post('/warnings/delete-all/{id}/{username?}', [WarningController::class, 'deleteAllWarnings'])->name('warnings.deleteAll');
    
    Route::post('/warnings/delete/{id}', [WarningController::class, 'deleteWarning'])->name('warnings.delete');
   
    Route::post('/warnings/restore/{id}', [WarningController::class, 'restoreWarning'])->name('warnings.restore');
});

use App\Http\Controllers\InviteController;

Route::middleware('auth')->group(function () {
  
    Route::post('/invites/create', [InviteController::class, 'createInvite'])->name('invites.create'); // To create an invite
    Route::post('/invite/use', [InviteController::class, 'useInvite'])->name('invite.use'); // To use an invite code
    Route::get('/invites', [InviteController::class, 'showInvites'])->name('invites.index'); // To show all invites
    Route::delete('/invites/{invite}', [InviteController::class, 'deleteInvite'])->name('invites.delete');

});

use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketResponseController;

Route::prefix('tickets')->name('tickets.')->middleware('auth')->group(function () {
    Route::get('create', [TicketController::class, 'create'])->name('create');
    Route::post('store', [TicketController::class, 'store'])->name('store');
    Route::get('{ticket}', [TicketController::class, 'show'])->name('show');
    Route::get('/', [TicketController::class, 'index'])->name('index');
    
    
    Route::post('{ticket}/response', [TicketResponseController::class, 'store'])->name('storeResponse');

    
});

Route::middleware(['auth'])->group(function () {
    Route::post('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');
    Route::post('/tickets/{ticket}/close', [TicketController::class, 'closeTicket'])->name('tickets.closeTicket');
    Route::delete('/tickets/{ticketId}', [TicketController::class, 'destroy'])->name('tickets.destroy');
});

// Route to show the edit form
Route::get('/tickets/{ticket}/responses/{response}/edit', [TicketController::class, 'editResponse'])->name('tickets.editResponse');

// Route to update the response (via POST)
Route::post('/tickets/{ticket}/responses/{response}', [TicketController::class, 'updateResponse'])->name('tickets.updateResponse');

Route::delete('/tickets/{ticket}/responses/{response}/delete', [TicketController::class, 'deleteResponse'])->name('tickets.deleteResponse');


//Coder Page
Route::get('/coder', [App\Http\Controllers\CoderController::class, 'index'])->name('coder.index')->middleware('auth');
Route::middleware(['auth'])->group(function () {
   Route::get('/coder/torrents', [\App\Http\Controllers\CoderController::class, 'torrents'])->name('coder.torrents');

});




