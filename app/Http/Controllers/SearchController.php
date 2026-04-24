<?php

namespace App\Http\Controllers;

use App\Services\MangaApiService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected MangaApiService $api) {}

    public function index(Request $request)
    {
        $query = $request->get('q', '');
        $results = [];
        $genres = [];

        $genresData = $this->api->getGenres();
        $genres = $genresData['genres'] ?? [];

        if (!empty($query)) {
            $data = $this->api->searchManga($query);
            $results = $data['manga_list'] ?? $data['results'] ?? [];
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'results' => $results,
                'query' => $query,
                'count' => count($results),
            ]);
        }

        return view('search.index', compact('query', 'results', 'genres'));
    }
}
