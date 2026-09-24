<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\MessagesController;
use App\Http\Controllers\Admin\MovieController as AdminMovieController;
use App\Http\Controllers\Admin\SeriesController as AdminSeriesController;
use App\Http\Controllers\Admin\SystemInfoController;
use App\Http\Controllers\Admin\TorrentLogController;
use App\Http\Controllers\Admin\TorrentsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnnounceController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\ResetPasswordController as AuthResetPasswordController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\Admin\HitRunAmnestyController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\ForumCategoryController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ForumPostLikeController;
use App\Http\Controllers\HappyHourController;
use App\Http\Controllers\HitAndRunController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\PostmarkController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPassword\ResetPasswordController;
use App\Http\Controllers\RssFeedController;
use App\Http\Controllers\SeedboxController;
use App\Http\Controllers\SeriesController;
use App\Http\Controllers\ShoutboxController;
use App\Http\Controllers\SnatchController;
use App\Http\Controllers\StaffTicketController;
use App\Http\Controllers\SubtitleController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketDashboardController;
use App\Http\Controllers\TicketReplyController;
use App\Http\Controllers\TopicSubscriptionController;
use App\Http\Controllers\TorrentController;
use App\Http\Controllers\TorrentHistoryController;
use App\Http\Controllers\TorrentMovieController;
use App\Http\Controllers\TorrentSeriesController;
use App\Http\Controllers\TorrentRequestController;
use App\Http\Controllers\UploadApplicationCommentController;
use App\Http\Controllers\UploadApplicationController;
use App\Http\Controllers\UploadApplicationVoteController;
use App\Http\Controllers\WarningController;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// Other imports

// Forum routes

Route::get('/forum', [ForumController::class, 'index'])
    ->name('forum.index');

Route::get('/forum/categories/create', [ForumCategoryController::class, 'create'])
    ->name('forum.category.create');

Route::post('/forum/categories', [ForumCategoryController::class, 'store'])
    ->name('forum.category.store');

Route::get('/forum/categories/{category}/edit', [ForumCategoryController::class, 'edit'])
    ->name('forum.category.edit');

Route::put('/forum/categories/{category}', [ForumCategoryController::class, 'update'])
    ->name('forum.category.update');

Route::delete('/forum/categories/{category}', [ForumCategoryController::class, 'destroy'])
    ->name('forum.category.destroy');

Route::patch('/forum/categories/{category}/restore', [ForumCategoryController::class, 'restore'])
    ->name('forum.category.restore');

Route::delete('/forum/categories/{category}/permanent', [ForumCategoryController::class, 'forceDestroy'])
    ->name('forum.category.force-delete');

Route::middleware('auth')->group(function () {

    Route::get('/forum/{category:slug}/create',
        [ForumController::class, 'create'])
        ->name('forum.topic.create');

    Route::post('/forum/{category:slug}',
        [ForumController::class, 'store'])
        ->name('forum.topic.store')
        ->middleware('throttle:6,1');

    Route::post('/forum/{category:slug}/{topic:slug}/reply',
        [ForumController::class, 'reply'])
        ->name('forum.topic.reply')
        ->middleware('throttle:10,1');

    Route::get('/forum/{category:slug}/{topic:slug}/post/{post}/edit',
        [ForumController::class, 'editPost'])
        ->name('forum.post.edit');

    Route::put('/forum/{category:slug}/{topic:slug}/post/{post}',
        [ForumController::class, 'updatePost'])
        ->name('forum.post.update');

    Route::delete('/forum/{category:slug}/{topic:slug}/post/{post}',
        [ForumController::class, 'deletePost'])
        ->name('forum.post.delete');

    Route::post('/forum/{category:slug}/{topic:slug}/lock',
        [ForumController::class, 'toggleLock'])
        ->name('forum.topic.lock');

    Route::post('/forum/{category:slug}/{topic:slug}/pin',
        [ForumController::class, 'togglePin'])
        ->name('forum.topic.pin');

    Route::delete('/forum/{category:slug}/{topic:slug}',
        [ForumController::class, 'deleteTopic'])
        ->name('forum.topic.delete');

    Route::post(
        '/forum/{category:slug}/{topic:slug}/post/{post}/like',
        [ForumPostLikeController::class, 'toggle']
    )->name('forum.post.like')
     ->middleware('throttle:30,1');

});

/*
|--------------------------------------------------------------------------
| Forum Static Routes (must be before wildcard {category:slug} routes)
|--------------------------------------------------------------------------
*/
Route::get('/forum/search', [ForumController::class, 'search'])
    ->name('forum.search');

Route::get('/forum/my-topics', [ForumController::class, 'myTopics'])
    ->middleware('auth')
    ->name('forum.my-topics');

/*
|--------------------------------------------------------------------------
| Forum Follow/Unfollow (authenticated, wildcard routes)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post(
        '/forum/{category:slug}/{topic:slug}/follow',
        [TopicSubscriptionController::class, 'store']
    )->name('forum.topic.follow');

    Route::delete(
        '/forum/{category:slug}/{topic:slug}/follow',
        [TopicSubscriptionController::class, 'destroy']
    )->name('forum.topic.unfollow');

});

/*
|--------------------------------------------------------------------------
| Forum Wildcard Routes (category / topic — must come last)
|--------------------------------------------------------------------------
*/
Route::get('/forum/{category:slug}/{topic:slug}',
    [ForumController::class, 'topic'])
    ->name('forum.topic');

Route::get('/forum/{category:slug}',
    [ForumController::class, 'category'])
    ->name('forum.category');

// Admin email routes

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/emails', [EmailController::class, 'index'])
        ->name('emails.index');

    Route::get('/emails/create', [EmailController::class, 'create'])
        ->name('emails.create');

    Route::post('/emails/send', [EmailController::class, 'send'])
        ->name('emails.send');

    Route::post('/emails/count', [EmailController::class, 'count'])
        ->name('emails.count');

    // ✅ DELETE OLD
    Route::delete('/emails/delete-old', [EmailController::class, 'deleteOld'])
        ->name('emails.delete-old');

    // ✅ DELETE SINGLE
    Route::delete('/emails/{email}', [EmailController::class, 'destroy'])
        ->name('emails.destroy');

});

// Notification routes

Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');

Route::post('/notifications/{id}/read', function ($id) {
    $notification = auth()->user()
        ->notifications()
        ->where('id', $id)
        ->firstOrFail();

    // Mark as read
    $notification->markAsRead();

    // ✅ SAFE redirect with fallback reconstruction
    if (! empty($notification->data['url'])) {
        return redirect($notification->data['url']);
    }

    // 🛟 Fallback for OLD notifications
    if (! empty($notification->data['topic_id'])) {
        return redirect(
            route('topics.show', $notification->data['topic_id'])
            .(! empty($notification->data['post_id'])
                ? '#post-'.$notification->data['post_id']
                : '')
        );
    }

    // Final fallback
    return redirect()->route('notifications.index');

})->middleware('auth')->name('notifications.read');

Route::post('/notifications/read-all', function () {
    auth()->user()->unreadNotifications->markAsRead();

    return back()->with('success', 'All notifications marked as read.');
})->middleware('auth')->name('notifications.readAll');

Route::delete('/notifications/{notification}', function ($id) {
    auth()->user()
        ->notifications()
        ->where('id', $id)
        ->firstOrFail()
        ->delete();

    return back()->with('success', 'Notification deleted.');
})->middleware('auth')->name('notifications.delete');

Route::delete('/notifications', function () {
    auth()->user()->notifications()->delete();

    return back()->with('success', 'All notifications deleted.');
})->middleware('auth')->name('notifications.deleteAll');

// Seedbox routes
Route::get('/seedboxes/{seedbox}/test-rpc/{hash}', [SeedboxController::class, 'testRpc'])->middleware('auth');

Route::get('/seedboxes/{seedbox}/torrent/{hash}/download', [SeedboxController::class, 'downloadTorrent'])->name('seedboxes.downloadTorrent');

Route::get('seedboxes/{seedbox}/torrent/{hash}/download-rebuilt', [SeedboxController::class, 'downloadRebuiltTorrent'])
    ->name('seedboxes.downloadRebuiltTorrent');

// Happy hour administration routes
Route::prefix('admin/happyhour')
    ->name('happyhour.')
    ->middleware(['auth', 'admin'])
    ->group(function () {

        Route::get('/', [HappyHourController::class, 'index'])->name('index');
        Route::get('/create', [HappyHourController::class, 'create'])->name('create');
        Route::post('/store', [HappyHourController::class, 'store'])->name('store');

        Route::post('/toggle-automatic', [HappyHourController::class, 'toggleAutomatic'])
            ->name('toggleAutomatic');

        Route::patch('/stop/{happyHour}', [HappyHourController::class, 'stop'])
            ->name('stop');

        // ✅ DELETE ROUTE
        Route::delete('/delete/{happyHour}', [HappyHourController::class, 'destroy'])
            ->name('destroy');
    });

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

// Snatch and seeding routes
Route::prefix('snatch')->group(function () {
    Route::get('/snatchlist/{userId?}', [SnatchController::class, 'snatchlist'])->name('snatch.snatchlist')->middleware('auth');
    Route::get('/seeding/{userId?}', [SnatchController::class, 'seeding'])->name('snatch.seeding')->middleware('auth');
    Route::get('/leeching/{userId?}', [SnatchController::class, 'leeching'])->name('snatch.leeching')->middleware('auth');
    Route::get('/hit-and-run/{userId?}', [SnatchController::class, 'hitAndRun'])->name('snatch.hitAndRun')->middleware('auth');
    Route::get('/need-to-seed/{userId?}', [SnatchController::class, 'needToSeed'])->name('snatch.needToSeed')->middleware('auth');

    Route::get('/hnr-fixer/{userId?}', [SnatchController::class, 'hitRunFixer'])->name('snatch.hnrFixer')->middleware('auth');

    Route::delete('/delete-need-to-seed/{userId}/{torrentId}', [SnatchController::class, 'deleteNeedToSeed'])
        ->name('snatch.deleteNeedToSeed')
        ->middleware('auth');

    Route::delete('/delete-hnr/{userId}/{torrentId}', [SnatchController::class, 'deleteHNR'])
        ->name('snatch.deleteHNR')
        ->middleware('auth');
});

// Emoji routes
Route::get('/get-emoji/{emojiCode}', function ($emojiCode) {
    return response()->json(['emoji' => emoji($emojiCode)]);
});

// Account verification routes
Route::get('/verify/{token}', function ($token) {

    $user = User::where('remember_token', $token)->first();

    if (! $user) {
        dd('USER NOT FOUND', $token);
    }

    $user->remember_token = null;
    $user->enabled = 'yes';
    $user->email_verified_at = now();

    $user->save();

    // Debug after update
    $user->refresh();

    return redirect('/login')->with('status', 'Email verified! You can now log in to your account.');
});

// Admin torrent log routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/torrent-logs', [TorrentLogController::class, 'index'])->name('admin.torrent_logs.index');
    Route::get('/torrent-logs/{id}', [TorrentLogController::class, 'show'])->name('admin.torrent_logs.show');

    // Hit & Run Amnesty
    Route::get('/hitrun-amnesty', [HitRunAmnestyController::class, 'index'])
        ->name('admin.hitrun_amnesty.index');
    Route::post('/hitrun-amnesty', [HitRunAmnestyController::class, 'run'])
        ->name('admin.hitrun_amnesty.run');
});

// Hit and run routes
Route::get('/hitandrun', [HitAndRunController::class, 'index'])->name('hitandrun.index')->middleware('auth');
// View another user's hit and run status
Route::get('/hitandrun/{userId}', [HitAndRunController::class, 'showOtherUserHitAndRun'])->name('hitandrun.showOther')->middleware('auth');

// Bonus purchase routes
Route::post('/bonus/buy-vip', [BonusController::class, 'buyVip'])->name('bonus.buyVip')->middleware('auth');

Route::post('/bonus/buy-seedtime', [BonusController::class, 'buySeedtime'])->name('bonus.buySeedtime')->middleware('auth');
Route::post('/remove-hnr', [BonusController::class, 'removeHNR'])->name('bonus.removeHNR')->middleware('auth');
Route::post('/buy-invites', [BonusController::class, 'buyInvites'])->name('buy.invites')->middleware('auth');
Route::post('/buy-slots', [BonusController::class, 'buySlots'])->name('buy.slots')->middleware('auth');
Route::post('/bonus/surprise', [BonusController::class, 'buySurprise'])->name('bonus.surprise')->middleware('auth');
Route::post('/bonus/buy-clear-hnr', [BonusController::class, 'clearOneHnr'])->name('bonus.clearHnr')->middleware('auth');
Route::post('/bonus/buy-reset-warning', [BonusController::class, 'buyResetWarning'])->name('bonus.resetWarning')->middleware('auth');

// Team route
Route::get('/team', [TeamController::class, 'index'])->name('team.index')->middleware('auth');

Auth::routes();
// Home and password recovery routes
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('last_activity')->middleware('auth');
Route::get('/recover-password', [AuthResetPasswordController::class, 'showRecoveryForm'])->name('password.recover');
Route::post('/password/reset', [AuthResetPasswordController::class, 'updatePassword'])->name('password.update');

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

// Slots
Route::get('/profile/{id}/{name}/tokens', [ProfileController::class, 'activeTokens'])
    ->name('profile.tokens')
    ->middleware('auth');

Route::delete('/profile/torrents/{torrent}/delete', [ProfileController::class, 'destroyTorrent'])
    ->name('profile.torrents.destroy')
    ->middleware('auth');

Route::delete('/profile/{id}/{name}/delete', [ProfileController::class, 'deleteAccount'])
    ->name('profile.delete')
    ->middleware('auth');

Route::patch('/profile/{id}/passkey/regenerate',
    [ProfileController::class, 'regeneratePasskey']
)->name('profile.passkey.regenerate')->middleware('auth');

Route::get('/profile/{id}/{name}/posts', [ProfileController::class, 'forumPosts'])->name('profile.posts');
Route::get('/profile/{id}/{name}/comments', [ProfileController::class, 'comments'])->name('profile.comments');
Route::get('/profile/{id}/{name}/thanks', [ProfileController::class, 'thanks'])->name('profile.thanks');

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

Route::delete('/movies/{id}/delete', [MovieController::class, 'destroy'])
    ->name('movies.delete')
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

Route::delete('/series/{id}/delete', [SeriesController::class, 'destroy'])
    ->name('series.delete')
    ->middleware('auth');

// Collections
Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index')->middleware('auth');
Route::get('/collections/{id}', [CollectionController::class, 'show'])->name('collections.show')->middleware('auth');

// Comments

Route::post('/comments', [CommentController::class, 'store'])->middleware('auth')->name('comments.store')->middleware('auth');
Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->middleware('auth')->name('comments.destroy')->middleware('auth');
Route::put('/comments/{id}/update', [CommentController::class, 'update'])->name('comments.update')->middleware('auth');

// Admin System
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {

    Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    Route::name('admin.')->group(function () {
        // Users Management
        Route::group(['prefix' => 'users'], function () {
            Route::get('/', [UserController::class, 'index'])->name('users.index');
            Route::get('/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/store', [UserController::class, 'store'])->name('users.store');
            Route::get('/{name}', [UserController::class, 'show'])->name('users.show');
            Route::get('/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/{id}/update', [UserController::class, 'update'])->name('users.update');
            Route::delete('/{id}/destroy', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('/admin/users/sendMassMessage', [UserController::class, 'sendMassMessage'])->name('users.sendMassMessage');
            Route::get('/users/comments', [UserController::class, 'comments'])->name('users.comments');

            Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');

            Route::delete('/admin/users/{id}/force', [UserController::class, 'deletePermanently'])->name('users.forceDelete');

        });

        // Movies Management
        Route::group(['prefix' => 'movies'], function () {
            Route::get('/', [AdminMovieController::class, 'index'])->name('movies.index');
            Route::get('/create', [AdminMovieController::class, 'create'])->name('movies.create');
            Route::post('/store', [AdminMovieController::class, 'store'])->name('movies.store');
            Route::get('/{id}/edit', [AdminMovieController::class, 'edit'])->name('movies.edit');
            Route::put('/{id}/update', [AdminMovieController::class, 'update'])->name('movies.update');
            Route::delete('/{id}/destroy', [AdminMovieController::class, 'destroy'])->name('movies.destroy');
        });

        // Series Management
        Route::group(['prefix' => 'series'], function () {
            Route::get('/', [AdminSeriesController::class, 'index'])->name('series.index');
            Route::get('/create', [AdminSeriesController::class, 'create'])->name('series.create');
            Route::post('/store', [AdminSeriesController::class, 'store'])->name('series.store');
            Route::get('/{id}/edit', [AdminSeriesController::class, 'edit'])->name('series.edit');
            Route::put('/{id}/update', [AdminSeriesController::class, 'update'])->name('series.update');
            Route::delete('/{id}/destroy', [AdminSeriesController::class, 'destroy'])->name('series.destroy');
        });

        // Torrents Management
        Route::group(['prefix' => 'torrents'], function () {
            Route::get('/', [TorrentsController::class, 'index'])->name('torrents.index');
            Route::get('/{id}', [TorrentsController::class, 'show'])->name('torrents.show');
            Route::get('/create', [TorrentsController::class, 'create'])->name('torrents.create');
            Route::post('/store', [TorrentsController::class, 'store'])->name('torrents.store');
            Route::get('/{id}/edit', [TorrentsController::class, 'edit'])->name('torrents.edit');
            Route::put('/{id}/update', [TorrentsController::class, 'update'])->name('torrents.update');
            Route::delete('/{id}/destroy', [TorrentsController::class, 'destroy'])->name('torrents.destroy');
            Route::delete('/{id}/force', [TorrentsController::class, 'forceDelete'])->name('torrents.forceDelete');
            Route::post('/{torrent}/restore', [TorrentsController::class, 'restore'])->withTrashed()->name('torrents.restore');
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

// Torrent routes
// Announce route
Route::get('/announce/{passkey}', [AnnounceController::class, 'announce'])
    ->withoutMiddleware([
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
    ])
    ->name('announce');

// Authenticated torrent routes
Route::middleware('auth')->group(function () {

    // ---------------------------------
    // Browse / CRUD
    // ---------------------------------
    Route::get('torrents', [TorrentController::class, 'index'])->name('torrents.index');
    Route::get('torrents/create', [TorrentController::class, 'create'])->name('torrents.create');
    Route::post('torrents', [TorrentController::class, 'store'])->name('torrents.store');

    Route::get('torrents/{id}/{slug}/edit', [TorrentController::class, 'edit'])->name('torrents.edit');
    Route::put('torrents/{torrent}', [TorrentController::class, 'update'])->name('torrents.update');

    // ---------------------------------
    // Soft Delete (Moderator)
    // ---------------------------------
    Route::delete('torrents/{torrent}', [TorrentController::class, 'destroy'])
        ->name('torrents.destroy');

    // ---------------------------------
    // Restore (Moderator)
    // ---------------------------------
    Route::post('torrents/{id}/restore', [TorrentController::class, 'restore'])
        ->name('torrents.restore');

    // ---------------------------------
    // Force Delete (Admin Only)
    // ---------------------------------
    Route::delete('torrents/{id}/force-delete', [TorrentController::class, 'forceDelete'])
        ->name('torrents.forceDelete');

    // ---------------------------------
    // Deleted Torrents Page (Optional Admin Panel)
    // ---------------------------------
    Route::get('torrents/deleted/list', [TorrentController::class, 'deleted'])
        ->name('torrents.deleted');

    // ---------------------------------
    // Adult Section
    // ---------------------------------
    Route::get('/torrents/adult', [TorrentController::class, 'adult'])
        ->name('torrents.adult');

    // ---------------------------------
    // Thank / Slots / Bump
    // ---------------------------------
    Route::post('torrents/{id}/thank', [TorrentController::class, 'thank'])
        ->name('torrents.thank');

    Route::post('torrents/{torrent}/react', [TorrentController::class, 'react'])
        ->name('torrents.react');

    // ---------------------------------
    // Subscribe / Unsubscribe (by title)
    // ---------------------------------
    Route::post('torrents/{id}/subscribe', [TorrentController::class, 'subscribe'])
        ->name('torrents.subscribe');

    Route::post('torrents/{id}/unsubscribe', [TorrentController::class, 'unsubscribe'])
        ->name('torrents.unsubscribe');

    Route::post('/slots/renew/{slotId}', [TorrentController::class, 'renewSlot'])
        ->name('slots.renew');

    Route::post('/slots/remove/{slotId}', [TorrentController::class, 'removeSlot'])
        ->name('slots.remove');

    Route::post('/torrents/bump/{id}', [TorrentController::class, 'bump'])
        ->name('torrents.bump');

    Route::delete('/torrents/bulk-delete', [TorrentController::class, 'bulkDelete'])
        ->name('torrents.bulkDelete');

});

// ---------------------------------
// Public torrent routes
// ---------------------------------

Route::get('/torrents/download/{id}/{slug}', [TorrentController::class, 'download'])
    ->whereNumber('id')
    ->name('torrents.download');

Route::get('/torrents/check-imdb', [TorrentController::class, 'checkImdbUrl']);

Route::get('/torrents/{id}/{slug?}', [TorrentController::class, 'show'])
    ->whereNumber('id')
    ->name('torrents.show')
    ->middleware('auth');

Route::get('/torrent/{torrent}/peers', [TorrentController::class, 'peers'])
    ->name('torrent.peers')
    ->middleware('auth');

Route::get('/torrents/{id}/{slug}/history', [TorrentHistoryController::class, 'index'])
    ->name('torrent.history')
    ->middleware('auth');

// RSS routes

Route::get('/rss', [RssFeedController::class, 'index'])->name('rss.index')->middleware('auth'); // Show the form
Route::get('/rss/feed', [RssFeedController::class, 'generateFeed'])->name('rss.feed'); // Generate the RSS feed
Route::get('/rss/download/{fileName}/{passkey}', [RssFeedController::class, 'downloadrss'])->name('rss.download');

// RSS routes

// Bonus shop routes

// Route to show the shop
Route::get('/shop', [BonusController::class, 'showShop'])->name('shop')->middleware('auth');

// Route for purchasing upload space (POST method)
Route::post('/shop', [BonusController::class, 'buyUpload'])->name('shop.upload')->middleware('auth');

// Donation route
Route::get('/donate', [DonationController::class, 'index'])->name('donate');

// Rules route
Route::get('/rules', function () {
    return view('rules');
})->middleware('auth')->name('rules');

// Shoutbox routes

Route::get('/shoutbox', [ShoutboxController::class, 'index'])->name('shoutbox.index')->middleware('auth');
Route::post('/shoutbox', [ShoutboxController::class, 'store'])->name('shoutbox.store')->middleware('auth');
Route::get('/shoutbox/{id}/edit', [ShoutboxController::class, 'edit'])->name('shoutbox.edit')->middleware('auth');
Route::put('/shoutbox/{id}', [ShoutboxController::class, 'update'])->name('shoutbox.update')->middleware('auth');
Route::delete('/shoutbox/{id}', [ShoutboxController::class, 'destroy'])->name('shoutbox.destroy')->middleware('auth');
Route::get('/shoutbox/{id}/reply', [ShoutboxController::class, 'showReplyForm'])->name('shoutbox.showReplyForm')->middleware('auth');
Route::post('/shoutbox/{id}/reply', [ShoutboxController::class, 'reply'])->name('shoutbox.reply')->middleware('auth');

// Private message routes — modern conversation messenger (single two-pane app)
Route::middleware(['auth'])->group(function () {
    Route::get('messages', [ConversationController::class, 'index'])->name('messages.index');
    Route::get('messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::post('messages/{conversation}/reply', [MessageController::class, 'storeReply'])->name('messages.storeReply');
    Route::get('messages/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');
    Route::delete('messages/{conversation}', [MessageController::class, 'destroyConversation'])->name('messages.destroyConversation');
});

// News routes

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

// Poll routes

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

Route::delete('/polls/{poll}/force-delete', [PollController::class, 'forceDelete'])
    ->name('polls.force-delete');

Route::post('/polls/{poll}/restore', [PollController::class, 'restore'])
    ->name('polls.restore');

Route::patch('/polls/{poll}/toggle', [PollController::class, 'toggle'])
    ->name('polls.toggle');

// Torrent request routes

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

// Warning routes
Route::middleware(['auth'])->group(function () {
    Route::get('/warnings', [WarningController::class, 'index'])->name('warnings.index');
    Route::get('/warnings/{id}/{username?}', [WarningController::class, 'show'])->name('warnings.show');
    Route::post('/warnings/deactivate/{id}', [WarningController::class, 'deactivate'])->name('warnings.deactivate');
    Route::post('/warnings/deactivate-all/{id}/{username?}', [WarningController::class, 'deactivateAllWarnings'])->name('warnings.deactivateAll');
    Route::post('/warnings/delete-all/{id}/{username?}', [WarningController::class, 'deleteAllWarnings'])->name('warnings.deleteAll');

    Route::post('/warnings/delete/{id}', [WarningController::class, 'deleteWarning'])->name('warnings.delete');

    Route::post('/warnings/restore/{id}', [WarningController::class, 'restoreWarning'])->name('warnings.restore');
});

Route::middleware('auth')->group(function () {

    Route::post('/invites/create', [InviteController::class, 'createInvite'])->name('invites.create'); // To create an invite
    Route::post('/invite/use', [InviteController::class, 'useInvite'])->name('invite.use'); // To use an invite code
    Route::get('/invites', [InviteController::class, 'showInvites'])->name('invites.index'); // To show all invites
    Route::delete('/invites/{invite}', [InviteController::class, 'deleteInvite'])->name('invites.delete');

});

// Subtitle routes
Route::post('/torrents/{torrent}/subtitles', [SubtitleController::class, 'store'])
    ->middleware('auth')
    ->name('subtitles.store');

Route::get('/subtitles/{subtitle}/download', [SubtitleController::class, 'download'])
    ->middleware('auth')
    ->name('subtitles.download');

Route::delete('/subtitles/{subtitle}', [SubtitleController::class, 'destroy'])
    ->middleware('auth')
    ->name('subtitles.destroy');

Route::get(
    '/subtitle/download/{id}',
    [SubtitleController::class, 'download']
)->name('subtitle.download');

// Admin message routes
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->name('admin.')
    ->group(function () {
        Route::get('messages', [MessagesController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [MessagesController::class, 'show'])->name('messages.show');
        Route::delete('messages/{message}', [MessagesController::class, 'destroy'])->name('messages.destroy');
        Route::post('messages/bulk', [MessagesController::class, 'bulk'])->name('messages.bulk');

    });

// Legal and cookie-consent routes

Route::view('/terms-of-service', 'legal.terms')
    ->name('terms.of.service');

Route::view('/privacy-policy', 'legal.privacy')
    ->name('privacy.policy');

Route::post('/cookie-consent', function (Request $request) {

    if (auth()->check()) {
        auth()->user()->update([
            'cookie_consent' => $request->value,
            'cookie_consent_at' => now(),
        ]);
    }

    return response()->json(['status' => 'ok']);
})->name('cookie.consent');
// Legal Terms

// Ticket routes

Route::middleware('auth')->group(function () {

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');

    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');

    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');

    /*
    |--------------------------------------------------------------------------
    | Redirect OLD ticket URLs
    |--------------------------------------------------------------------------
    */

    Route::get('/tickets/{id}', function ($id) {

        $ticket = Ticket::find($id);

        if (! $ticket) {
            return redirect()->route('tickets.index');
        }

        return redirect()->route('tickets.show', [
            'id' => $ticket->id,
            'slug' => $ticket->slug,
        ]);

    });

    Route::get('/ticket/TK{id}-{slug}', [TicketController::class, 'show'])->name('tickets.show');

    Route::post('/tickets/{id}/reply', [TicketReplyController::class, 'store'])->name('tickets.reply');

    Route::post('/tickets/{id}/claim', [StaffTicketController::class, 'claim'])->name('tickets.claim');

    Route::post('/tickets/{id}/assign', [StaffTicketController::class, 'assign'])->name('tickets.assign');

    Route::post('/tickets/{id}/status', [StaffTicketController::class, 'changeStatus'])->name('tickets.status');

    Route::post('/tickets/{id}/lock', [StaffTicketController::class, 'lock'])->name('tickets.lock');

    Route::get('/staff/tickets/dashboard', [TicketDashboardController::class, 'index'])->name('tickets.dashboard');

    Route::get('/staff/tickets/my', [StaffTicketController::class, 'myTickets'])->name('tickets.my');

    Route::get('/staff/tickets/unassigned', [StaffTicketController::class, 'unassigned'])->name('tickets.unassigned');

    Route::get('/tickets/{id}/replies', [TicketReplyController::class, 'fetch'])->name('tickets.fetchReplies');

    Route::get('/ticket/attachment/{id}', [TicketReplyController::class, 'download'])
        ->name('tickets.download')
        ->middleware('auth');

    Route::post('/tickets/{id}/typing', [TicketReplyController::class, 'typing']);
    Route::get('/tickets/{id}/typing-status', [TicketReplyController::class, 'typingStatus']);

    Route::post('/tickets/{id}/lock', [TicketController::class, 'lock'])->name('tickets.lock');

    Route::post('/tickets/{id}/unlock', [TicketController::class, 'unlock'])->name('tickets.unlock');

});

// Ticket routes

// Shoutbox presence routes
Route::post('/shoutbox/typing', [ShoutboxController::class, 'typing']);
Route::post('/shoutbox/typing-stop', [ShoutboxController::class, 'stopTyping']);
Route::get('/shoutbox/typing-users', [ShoutboxController::class, 'typingUsers']);
Route::get('/shoutbox/poll', [ShoutboxController::class, 'poll'])->middleware('auth');

/* Guest contact */

// Contact routes
Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::get('/contact/check', [ContactController::class, 'check'])->name('contact.check');
Route::post('/contact/replies', [ContactController::class, 'viewReply'])->name('contact.replies');
Route::post('/contact/reply/{id}', [ContactController::class, 'guestReply'])->name('contact.reply');

/* Staff */

Route::middleware(['auth'])->group(function () {

    Route::get('/contactstaff', [ContactController::class, 'index'])
        ->name('contactstaff.index');

    Route::get('/contactstaff/{id}', [ContactController::class, 'show'])
        ->name('contactstaff.show');

    Route::post('/contactstaff/{id}/answer', [ContactController::class, 'answer'])
        ->name('contactstaff.answer');

    Route::post('/contactstaff/{id}/resolve', [ContactController::class, 'resolve'])
        ->name('contactstaff.resolve');

});

Route::post('/admin/users/mass-message/preview', [UserController::class, 'previewMassMessage'])
    ->name('admin.users.mass-message.preview');

Route::post('/messages/edit/{message}', [MessageController::class, 'edit'])
    ->name('messages.edit');

Route::delete('/messages/delete/{message}', [MessageController::class, 'delete'])
    ->name('messages.delete');

Route::middleware(['auth'])->group(function () {

    Route::get('/upload-applications', [UploadApplicationController::class, 'index'])->name('uploadapps.index');
    Route::get('/upload-applications/create', [UploadApplicationController::class, 'create'])->name('uploadapps.create');
    Route::post('/upload-applications', [UploadApplicationController::class, 'store'])->name('uploadapps.store');

    Route::get('/upload-applications/{id}', [UploadApplicationController::class, 'show'])->name('uploadapps.show');

    Route::post('/upload-applications/{id}/comment', [UploadApplicationCommentController::class, 'store'])->name('uploadapps.comment');

    Route::post('/upload-applications/{id}/vote', [UploadApplicationVoteController::class, 'vote'])->name('uploadapps.vote');

    Route::post('/upload-applications/{id}/accept', [UploadApplicationController::class, 'accept'])->name('uploadapps.accept');

    Route::post('/upload-applications/{id}/reject', [UploadApplicationController::class, 'reject'])->name('uploadapps.reject');

});

Route::middleware('auth')->group(function () {

    // User
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

    Route::get('/announcements/{id}', [AnnouncementController::class, 'show'])
        ->whereNumber('id')
        ->name('announcements.show');

    Route::get('/announcements-unread-count', [AnnouncementController::class, 'unreadCount']);

    // Admin
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');

    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');

    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy'])
        ->whereNumber('id')
        ->name('announcements.destroy');

    Route::get('/announcements/{id}/edit', [AnnouncementController::class, 'edit'])
        ->name('announcements.edit');

    Route::put('/announcements/{id}', [AnnouncementController::class, 'update'])
        ->name('announcements.update');

    Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy'])
        ->name('announcements.destroy');

    Route::post('/announcements/{id}/restore', [AnnouncementController::class, 'restore'])
        ->name('announcements.restore');

    Route::delete('/announcements/{id}/force-delete', [AnnouncementController::class, 'forceDelete'])
        ->name('announcements.forceDelete');
});

// Library routes
Route::prefix('library')->group(function () {
    Route::get('/movies', [TorrentMovieController::class, 'index'])->name('library.movies.index');
    Route::get('/movies/{tmdbid}/{slug?}', [TorrentMovieController::class, 'show'])->name('library.movies.show');

    // Library subscribe/unsubscribe by TMDB title (authenticated only)
    Route::middleware('auth')->group(function () {
        Route::post('/movies/{tmdbid}/subscribe', [TorrentMovieController::class, 'subscribe'])->name('library.movies.subscribe');
        Route::post('/movies/{tmdbid}/unsubscribe', [TorrentMovieController::class, 'unsubscribe'])->name('library.movies.unsubscribe');
        Route::post('/series/{tmdbid}/subscribe', [TorrentSeriesController::class, 'subscribe'])->name('library.series.subscribe');
        Route::post('/series/{tmdbid}/unsubscribe', [TorrentSeriesController::class, 'unsubscribe'])->name('library.series.unsubscribe');
    });

    Route::get('/series', [TorrentSeriesController::class, 'index'])->name('library.series.index');
    Route::get('/series/{tmdbid}/season/{season}', [TorrentSeriesController::class, 'season'])->name('library.series.season');
    Route::get('/series/{tmdbid}/{slug?}', [TorrentSeriesController::class, 'show'])->name('library.series.show');
});

// Postmark webhook route
Route::post('/postmark/bounce', [PostmarkController::class, 'bounce'])
    ->withoutMiddleware([VerifyCsrfToken::class]);

// Account preference and availability routes

Route::post('/user/email-preferences', function (Request $request) {

    $user = auth()->user();

    $user->subscribed = $request->boolean('subscribed');
    $user->save();

    return back()->with('success', 'Email preferences updated.');
});

Route::get('/check-username', function (Request $request) {
    return response()->json([
        'exists' => User::where('name', $request->name)->exists(),
    ]);
});

Route::get('/check-email', function (Request $request) {
    return response()->json([
        'exists' => User::where('email', $request->email)->exists(),
    ]);
});
