<?php

namespace App\Http\Controllers;

use App\Services\MangaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

            // Enrich missing thumbnails using manga detail (cached).
            $results = array_values(array_map(function ($manga) {
                if (!is_array($manga)) {
                    return $manga;
                }

                $slug = trim((string) ($manga['endpoint'] ?? $manga['manga_endpoint'] ?? Str::slug($manga['title'] ?? 'manga')), '/');
                $thumb = $manga['thumb'] ?? $manga['image'] ?? null;

                if (($thumb === null || $thumb === '') && $slug !== '') {
                    $fallbackThumb = $this->api->getMangaThumb($slug);
                    if ($fallbackThumb) {
                        $manga['thumb'] = $fallbackThumb;
                    }
                }

                return $manga;
            }, $results));
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
