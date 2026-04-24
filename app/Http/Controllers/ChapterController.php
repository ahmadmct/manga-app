<?php

namespace App\Http\Controllers;

use App\Services\MangaApiService;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function __construct(protected MangaApiService $api) {}

    public function show(string $chapterSlug)
    {
        $chapterSlug = trim($chapterSlug, '/');
        $data = $this->api->getChapter($chapterSlug);

        if (isset($data['error']) || empty($data)) {
            abort(404);
        }

        $chapter = $data['chapter'] ?? $data;
        $images = $chapter['chapter_image'] ?? $chapter['images'] ?? [];
        $chapterTitle = $chapter['chapter_title'] ?? $chapterSlug;

        // Try to parse manga slug from chapter slug
        // typical format: manga-name-chapter-X-bahasa-indonesia
        $mangaSlug = $this->extractMangaSlug($chapterSlug);
        
        // Save to reading history
        $this->saveToHistory($chapterSlug, $chapterTitle, $mangaSlug);

        return view('chapter.show', compact('chapter', 'images', 'chapterSlug', 'chapterTitle', 'mangaSlug'));
    }

    private function extractMangaSlug(string $chapterSlug): string
    {
        // Remove chapter number suffix patterns
        $slug = preg_replace('/-chapter-[\d\w-]+(-bahasa-indonesia)?$/', '', $chapterSlug);
        return $slug ?: $chapterSlug;
    }

    private function saveToHistory(string $chapterSlug, string $chapterTitle, string $mangaSlug): void
    {
        $history = session('reading_history', []);

        // Remove duplicate
        $history = array_filter($history, fn($h) => ($h['chapter_slug'] ?? '') !== $chapterSlug);

        array_unshift($history, [
            'chapter_slug' => $chapterSlug,
            'chapter_title' => $chapterTitle,
            'manga_slug' => $mangaSlug,
            'read_at' => now()->toDateTimeString(),
        ]);

        session(['reading_history' => array_slice(array_values($history), 0, 50)]);
    }
}
