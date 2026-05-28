<?php

namespace App\Http\Controllers;

use App\Models\ReadingHistory;
use App\Services\MangaApiService;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    public function __construct(protected MangaApiService $api) {}

    public function index(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 24);
        $data = $this->api->getLatestMangaPerPage($page, $perPage);
        $manga = $data['manga_list'] ?? [];
        $perPage = (int) ($data['per_page'] ?? $perPage);

        return view('manga.index', compact('manga', 'page', 'perPage'));
    }

    public function show(string $slug)
    {
        $slug = trim($slug, '/');
        $data = $this->api->getMangaDetail($slug);

        if (isset($data['error']) || empty($data)) {
            abort(404);
        }

        $manga = $data['manga_detail'] ?? $data;
        $chapters = $manga['chapter_list'] ?? $manga['chapters'] ?? $manga['chapter'] ?? [];

        // Add to recently viewed
        $this->addToRecentlyViewed($manga, $slug);

        $bookmarks = session('bookmarks', []);
        $isBookmarked = in_array($slug, array_column($bookmarks, 'slug'));
        $readChapterSlugs = [];
        $lastRead = null;

        if (auth()->check()) {
            $histories = ReadingHistory::where('user_id', auth()->id())
                ->where('manga_slug', $slug)
                ->latest('updated_at')
                ->get();

            $readChapterSlugs = $histories->pluck('chapter_slug')
                ->map(fn ($chapterSlug) => trim((string) $chapterSlug, '/'))
                ->all();
            $lastRead = $histories->first();
        } else {
            $histories = collect(session('reading_history', []))
                ->filter(fn ($item) => is_array($item) && ($item['manga_slug'] ?? '') === $slug);

            $readChapterSlugs = $histories->pluck('chapter_slug')
                ->map(fn ($chapterSlug) => trim((string) $chapterSlug, '/'))
                ->all();
            $lastRead = $histories->first();
        }

        return view('manga.show', compact('manga', 'chapters', 'slug', 'isBookmarked', 'readChapterSlugs', 'lastRead'));
    }

    public function genre(string $genre, Request $request)
    {
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 24);
        $data = $this->api->getMangaByGenrePerPage($genre, $page, $perPage);
        $manga = $data['manga_list'] ?? [];
        $perPage = (int) ($data['per_page'] ?? $perPage);
        $genresData = $this->api->getGenres();
        $genres = $genresData['genres'] ?? [];

        return view('manga.genre', compact('manga', 'genre', 'page', 'perPage', 'genres'));
    }

    private function addToRecentlyViewed(array $manga, string $slug): void
    {
        $history = session('recently_viewed', []);
        
        // Remove existing entry if present
        $history = array_filter($history, fn($h) => ($h['slug'] ?? '') !== $slug);
        
        // Add to beginning
        array_unshift($history, [
            'slug' => $slug,
            'title' => $manga['title'] ?? 'Unknown',
            'thumb' => $manga['thumb'] ?? $manga['image'] ?? '',
            'type' => $manga['type'] ?? 'Manga',
            'viewed_at' => now()->toDateTimeString(),
        ]);

        // Keep only last 20
        session(['recently_viewed' => array_slice(array_values($history), 0, 20)]);
    }
}
