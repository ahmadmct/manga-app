<?php

namespace App\Http\Controllers;

use App\Models\ReadingHistory;
use App\Services\MangaApiService;

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

        // Resolve prev/next chapter slugs (prefer chapter payload, fallback to manga detail list)
        [$prevSlug, $nextSlug] = $this->resolvePrevNextChapterSlugs($chapter, $chapterSlug, $mangaSlug);
        
        // Save to reading history
        $this->saveToHistory($chapterSlug, $chapterTitle, $mangaSlug);

        return view('chapter.show', compact('chapter', 'images', 'chapterSlug', 'chapterTitle', 'mangaSlug', 'prevSlug', 'nextSlug'));
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

        if (auth()->check()) {
            ReadingHistory::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'chapter_slug' => $chapterSlug,
                ],
                [
                    'manga_slug' => $mangaSlug,
                    'chapter_title' => $chapterTitle,
                    'progress' => 0,
                ]
            );
        }
    }

    /**
     * @return array{0:string,1:string} [prevSlug, nextSlug]
     */
    private function resolvePrevNextChapterSlugs(array $chapter, string $chapterSlug, string $mangaSlug): array
    {
        $prevChapter = $chapter['prev_chapter'] ?? $chapter['previous_chapter'] ?? $chapter['chapter_prev'] ?? null;
        $nextChapter = $chapter['next_chapter'] ?? $chapter['chapter_next'] ?? null;

        $prevSlug = is_array($prevChapter)
            ? ($prevChapter['chapter_endpoint'] ?? $prevChapter['endpoint'] ?? '')
            : (is_string($prevChapter) ? $prevChapter : '');

        $nextSlug = is_array($nextChapter)
            ? ($nextChapter['chapter_endpoint'] ?? $nextChapter['endpoint'] ?? '')
            : (is_string($nextChapter) ? $nextChapter : '');

        $prevSlug = trim($prevSlug, '/');
        $nextSlug = trim($nextSlug, '/');

        if ($prevSlug !== '' || $nextSlug !== '') {
            return [$prevSlug, $nextSlug];
        }

        // Fallback: derive from manga detail chapter list
        $detail = $this->api->getMangaDetail($mangaSlug);
        $manga = $detail['manga_detail'] ?? $detail;
        $chapters = $manga['chapter_list'] ?? $manga['chapters'] ?? $manga['chapter'] ?? [];

        if (!is_array($chapters) || $chapters === []) {
            return ['', ''];
        }

        $currentSlug = trim((string) ($chapter['chapter_endpoint'] ?? $chapterSlug), '/');
        $currentIndex = null;

        foreach (array_values($chapters) as $i => $ch) {
            if (!is_array($ch)) {
                continue;
            }

            $slug = trim((string) ($ch['chapter_endpoint'] ?? $ch['endpoint'] ?? ''), '/');
            if ($slug !== '' && $slug === $currentSlug) {
                $currentIndex = $i;
                break;
            }
        }

        if ($currentIndex === null) {
            return ['', ''];
        }

        // Chapter lists from the API are commonly sorted newest -> oldest.
        // "Next" while reading (forward) should go to a newer chapter, i.e. towards the start of the list.
        $newerIndex = $currentIndex - 1;
        $olderIndex = $currentIndex + 1;

        $nextSlug = '';
        $prevSlug = '';

        if (isset($chapters[$newerIndex]) && is_array($chapters[$newerIndex])) {
            $nextSlug = trim((string) ($chapters[$newerIndex]['chapter_endpoint'] ?? $chapters[$newerIndex]['endpoint'] ?? ''), '/');
        }

        if (isset($chapters[$olderIndex]) && is_array($chapters[$olderIndex])) {
            $prevSlug = trim((string) ($chapters[$olderIndex]['chapter_endpoint'] ?? $chapters[$olderIndex]['endpoint'] ?? ''), '/');
        }

        return [$prevSlug, $nextSlug];
    }
}
