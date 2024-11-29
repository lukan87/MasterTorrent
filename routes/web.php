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
use App\Http\Controllers\PostController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ForumCategoryController;
use App\Http\Controllers\ShoutboxController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\ResetPassword\ResetPasswordController;
use App\Http\Controllers\TorrentRequestController;
use App\Http\Controllers\Admin\SystemInfoController;




Auth::routes();
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('last_activity');
Route::get('/recover-password', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showRecoveryForm'])->name('password.recover');
Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'updatePassword'])->name('password.update');



// Custom password recovery routes
Route::get('/custom-password/recover', [ResetPasswordController::class, 'showRecoveryForm'])->name('custom.password.recover');
Route::post('/custom-password/reset', [ResetPasswordController::class, 'updatePassword'])->name('custom.password.update');



// Show user profile by ID and name
Route::get('/profile/{id}/{name}', [ProfileController::class, 'show'])->name('profile.show')->middleware('auth');

// Edit user profile by ID and name
Route::get('/profile/{id}/{name}/edit', [ProfileController::class, 'edit'])->name('profile.edit')->middleware('auth');

// Update user profile by ID and name
Route::put('/profile/{id}/{name}', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');

// User's Torrents
Route::get('profile/{id}/{name}/torrents', [ProfileController::class, 'userTorrents'])->name('profile.torrents');

// Seeding Torrents
Route::get('/profile/{id}/{name}/seeding-torrents', [ProfileController::class, 'seedingTorrents'])->name('profile.seedingTorrents');


// Movies
// Resource route with authentication middleware applied
Route::resource('movies', MovieController::class)->middleware('auth');



// Additional custom routes that do not conflict with resource routes
Route::post('/movies/search', [MovieController::class, 'search'])->name('movies.search')->middleware('auth');
Route::get('/movies/select/{tmdb_id}', [MovieController::class, 'selectMovie'])->name('movies.select')->middleware('auth');
// Route::get('/movies/{id}', [MovieController::class, 'show'])->name('movies.show')->middleware('auth');
Route::post('/movies/bulk-select', [MovieController::class, 'bulkSelect'])->name('movies.bulkSelect')->middleware('auth');
Route::post('/movies/search-movie', [MovieController::class, 'searchmovie'])->name('movies.search-movie')->middleware('auth');



// Series
Route::resource('series', SeriesController::class)->middleware('auth');
Route::get('/series', [SeriesController::class, 'index'])->name('series.index')->middleware('auth');
Route::get('/series/create', [SeriesController::class, 'create'])->name('series.create')->middleware('auth');
Route::post('/series/search', [SeriesController::class, 'search'])->name('series.search')->middleware('auth');
Route::get('/series/select/{tmdb_id}', [SeriesController::class, 'selectSeries'])->name('series.select')->middleware('auth');
//Route::get('/series/{id}', [SeriesController::class, 'show'])->name('series.show')->middleware('auth');
Route::post('/series/bulk-select', [SeriesController::class, 'bulkSelect'])->name('series.bulkSelect')->middleware('auth');
Route::post('/series/search-movie', [SeriesController::class, 'searchSeries'])->name('series.search-series')->middleware('auth');

// Collections
Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index')->middleware('auth');
Route::get('/collections/{id}', [CollectionController::class, 'show'])->name('collections.show')->middleware('auth');

//Comments

Route::post('/comments', [CommentController::class, 'store'])->middleware('auth')->name('comments.store')->middleware('auth');
Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->middleware('auth')->name('comments.destroy')->middleware('auth');



// Admin System
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function () {

    Route::get('/', [App\Http\Controllers\Admin\AdminController::class, 'index'])->name('admin.index');
    Route::name('admin.')->group(function () {
        // Users Management
            Route::group(['prefix' => 'users'], function () {
            Route::get('/', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
            Route::get('/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
            Route::post('/store', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
            Route::get('/users/{name}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
            Route::get('/{id}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
            Route::put('/{id}/update', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
            Route::delete('/{id}/destroy', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
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
            Route::get('/show-routes', [SystemInfoController::class, 'showRoutes'])->name('systemInfo.showRoutes');
        });


    });
});



//Torrents
// Announce
Route::any('/announce/{passkey}', [AnnounceController::class, 'announce'])->name('announce');
// Group the routes under authentication middleware
Route::middleware('auth')->group(function () {
    Route::get('torrents', [TorrentController::class, 'index'])->name('torrents.index');        // Show all torrents
    Route::get('torrents/create', [TorrentController::class, 'create'])->name('torrents.create') ->middleware(['auth', \App\Http\Middleware\CheckPermission::class . ':can_upload']);// Show form to create a new torrent
    Route::post('torrents', [TorrentController::class, 'store'])->name('torrents.store');        // Store a new torrent
    // Route::get('torrents/{torrent}', [TorrentController::class, 'show'])->name('torrents.show');  // Show a specific torrent
    Route::get('torrents/{id}/{slug}/edit', [TorrentController::class, 'edit'])->name('torrents.edit'); // Show form to edit a torrent
    Route::put('torrents/{torrent}', [TorrentController::class, 'update'])->name('torrents.update'); // Update a specific torrent
    Route::delete('torrents/{torrent}', [TorrentController::class, 'destroy'])->name('torrents.destroy'); // Delete a specific torrent
    Route::get('/torrents/adult', [TorrentController::class, 'adult'])->name('torrents.adult');

});
Route::get('/torrents/{id}/{slug?}', [TorrentController::class, 'show'])->name('torrents.show')->middleware('auth');
Route::get('/torrents/download/{id}/{slug}', [TorrentController::class, 'download'])->name('torrents.download')->middleware('auth');
Route::get('/torrent/{torrent}/peers', [TorrentController::class, 'peers'])->name('torrent.peers');




//Bonus page//

// Route to show the shop
Route::get('/shop', [BonusController::class, 'showShop'])->name('shop')->middleware('auth');

// Route for purchasing upload space (POST method)
Route::post('/shop', [BonusController::class, 'buyUpload'])->name('shop.upload')->middleware('auth');



// Donate page beta //
Route::match(['get', 'post'], '/donate', function (Illuminate\Http\Request $request) {
    if ($request->isMethod('post')) {
        $amount = $request->input('amount');
        return back()->with('success', "Thank you for donating \${$amount}!");
    }

    return view('donate');
})->name('donate');

Route::prefix('forum')->group(function () {
    // Forum Category Routes
    Route::get('/categories', [ForumCategoryController::class, 'index'])->name('forum.categories.index')->middleware('permission:view_categories');
    Route::get('/categories/create', [ForumCategoryController::class, 'create'])->name('forum.categories.create')->middleware('permission:create_categories');
    Route::post('/categories', [ForumCategoryController::class, 'store'])->name('forum.categories.store')->middleware('permission:create_categories');
    Route::get('/categories/{category}', [ForumCategoryController::class, 'show'])->name('forum.categories.show');
    Route::post('/{categoryId}/store', [ForumController::class, 'store'])->name('forum.store');


    Route::get('/categories/{category}/edit', [ForumCategoryController::class, 'edit'])->name('forum.categories.edit')->middleware('permission:edit_categories');
    Route::put('/categories/{category}', [ForumCategoryController::class, 'update'])->name('forum.categories.update')->middleware('permission:edit_categories');
    Route::delete('/categories/{category}', [ForumCategoryController::class, 'destroy'])->name('forum.categories.destroy')->middleware('permission:delete_categories');

    // Topic Routes
    Route::get('/', [ForumController::class, 'index'])->name('forum.index')->middleware('permission:view_topics');
    Route::get('/create', [ForumController::class, 'create'])->name('forum.create')->middleware('permission:create_topics');
    // Route::post('/', [ForumController::class, 'store'])->name('forum.store')->middleware('permission:create_topics');
    Route::get('/{topic}', [ForumController::class, 'show'])->name('forum.show');
    Route::get('/{topic}/edit', [ForumController::class, 'edit'])->name('topics.edit')->middleware('permission:edit_posts');
    Route::delete('/{topic}', [ForumController::class, 'destroy'])->name('forum.destroy')->middleware('permission:delete_topics');
    Route::get('/{categoryId}/create', [ForumController::class, 'create'])->name('forum.create');


    // Post Routes
    Route::post('/{topic}/posts', [PostController::class, 'store'])->name('posts.store')->middleware('permission:reply');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit')->middleware('permission:edit_posts');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update')->middleware('permission:edit_posts');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy')->middleware('permission:delete_posts');
    Route::put('/{topic}', [ForumController::class, 'update'])->name('forum.update')->middleware('permission:edit_posts');
    Route::get('/topics/{id}', [ForumController::class, 'show'])->name('topics.show');
});


// Shoutbox//

//Shoutbox



Route::get('/shoutbox', [ShoutboxController::class, 'index'])->name('shoutbox.index');
Route::post('/shoutbox', [ShoutboxController::class, 'store'])->name('shoutbox.store');
Route::get('/shoutbox/{id}/edit', [ShoutboxController::class, 'edit'])->name('shoutbox.edit');
Route::put('/shoutbox/{id}', [ShoutboxController::class, 'update'])->name('shoutbox.update');
Route::delete('/shoutbox/{id}', [ShoutboxController::class, 'destroy'])->name('shoutbox.destroy');
Route::get('/shoutbox/{id}/reply', [ShoutboxController::class, 'showReplyForm'])->name('shoutbox.showReplyForm');
Route::post('/shoutbox/{id}/reply', [ShoutboxController::class, 'reply'])->name('shoutbox.reply');



Route::middleware(['auth'])->group(function () {
    Route::get('messages/inbox', [MessageController::class, 'inbox'])->name('messages.inbox');
    Route::get('messages/outbox', [MessageController::class, 'outbox'])->name('messages.outbox');
    Route::get('messages/create', [MessageController::class, 'create'])->name('messages.create');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('messages/{message}', [MessageController::class, 'show'])->name('messages.show');
    Route::delete('messages/{message}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');

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
Route::get('polls', [PollController::class, 'index'])->name('polls.index');

// Show the form for creating a new poll
Route::get('polls/create', [PollController::class, 'create'])->name('polls.create')->middleware('permission:create_polls');

// Store a newly created poll in storage
Route::post('polls', [PollController::class, 'store'])->name('polls.store');

// Display a specific poll
Route::get('polls/{poll}', [PollController::class, 'show'])->name('polls.show');

// Show the form for editing a specific poll
Route::get('polls/{poll}/edit', [PollController::class, 'edit'])->name('polls.edit')->middleware('permission:edit_polls');

// Update a specific poll in storage
Route::put('polls/{poll}', [PollController::class, 'update'])->name('polls.update');

// Delete a specific poll
Route::delete('polls/{poll}', [PollController::class, 'destroy'])->name('polls.destroy')->middleware('permission:delete_polls');

Route::post('polls/{poll}/vote', [PollController::class, 'vote'])->name('polls.vote');

Route::post('/polls/{pollId}/vote', [PollController::class, 'vote'])->name('polls.vote');

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
