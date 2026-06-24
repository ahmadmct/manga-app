<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\RecommendedController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\UnlockController;

Route::get('/unlock-step-1', [UnlockController::class, 'step1'])
    ->name('unlock.step1');

Route::post('/unlock-step-1', [UnlockController::class, 'step1Submit'])
    ->name('unlock.step1.submit');

Route::get('/unlock-step-2', [UnlockController::class, 'step2'])
    ->name('unlock.step2');

Route::post('/unlock-step-2', [UnlockController::class, 'step2Submit'])
    ->name('unlock.step2.submit');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('/users', UserController::class)->except(['show']);
});
Route::middleware('app.lock')->group(function () {
// Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Manga
Route::get('/manga', [MangaController::class, 'index'])->name('manga.index');
Route::get('/manga/{slug}', [MangaController::class, 'show'])->name('manga.show');

// Chapter reader
Route::get('/read/{chapterSlug}', [ChapterController::class, 'show'])->name('chapter.show');

// Search
Route::get('/search', [SearchController::class, 'index'])->name('search');

// Recommended
Route::get('/recommended', [RecommendedController::class, 'index'])->name('recommended.index');

// Genre
Route::get('/genre/{genre}', [MangaController::class, 'genre'])->name('manga.genre');

// Bookmarks (session-based)
Route::post('/bookmarks/toggle', [BookmarkController::class, 'toggle'])->name('bookmarks.toggle');
Route::get('/bookmarks', [BookmarkController::class, 'index'])->name('bookmarks.index');

// History
Route::post('/history/add', [BookmarkController::class, 'addHistory'])->name('history.add');
Route::get('/history', [BookmarkController::class, 'history'])->name('history.index');


Route::get('/img-proxy', [App\Http\Controllers\ImageProxyController::class, 'proxy'])
    ->name('img.proxy');
});
Route::get('/test-wa', function () {
    \App\Services\WhatsAppService::kirimWA('Test pesan dari Laravel');
    return 'Pesan WA dikirim, cek log untuk detailnya.';
});

Route::get('/test-email-job', function () {
    (new \App\Jobs\CheckEmailJob())->handle();

    return 'OK';
});

