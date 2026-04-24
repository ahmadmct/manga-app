<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\RecommendedController;

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
