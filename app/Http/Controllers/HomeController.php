<?php

namespace App\Http\Controllers;

use App\Services\MangaApiService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(protected MangaApiService $api) {}

    public function index(Request $request)
    {
        $latestData = $this->api->getLatestManga(1);
        // dd($latestData);
        $popularData = $this->api->getPopularManga(1);
        $genresData = $this->api->getGenres();
        $recommendedData = $this->api->getRecommendedManga(1);

        $latestManga = $latestData['manga_list'] ?? [];
        $popularManga = $popularData['manga_list'] ?? [];
        $recommendedManga = $recommendedData['manga_list'] ?? [];
        $genres = $genresData['genres'] ?? [];

        // Session history / continue reading
        $readingHistory = session('reading_history', []);
        $bookmarks = session('bookmarks', []);

        return view('home', compact(
            'latestManga',
            'popularManga',
            'recommendedManga',
            'genres',
            'readingHistory',
            'bookmarks'
        ));
    }
}
