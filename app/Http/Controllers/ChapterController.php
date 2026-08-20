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
        $mangaSlug = $this->resolveMangaSlug($chapter, $chapterSlug, $mangaSlug);

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

    private function resolveMangaSlug(array $chapter, string $chapterSlug, string $candidate): string
    {
        $currentSlug = trim((string) ($chapter['chapter_endpoint'] ?? $chapterSlug), '/');
        $detail = $this->api->getMangaDetail($candidate);
        $manga = $detail['manga_detail'] ?? $detail;
        $chapters = is_array($manga)
            ? ($manga['chapter_list'] ?? $manga['chapters'] ?? $manga['chapter'] ?? [])
            : [];

        foreach (is_array($chapters) ? $chapters : [] as $item) {
            $endpoint = is_array($item) ? trim((string) ($item['chapter_endpoint'] ?? $item['endpoint'] ?? ''), '/') : '';
            if ($endpoint !== '' && $endpoint === $currentSlug) {
                return $candidate;
            }
        }

        // Some APIs use a short chapter prefix (e.g. sword-king) that is
        // different from the manga endpoint. Resolve the real manga via search.
        $results = $this->api->searchManga(str_replace('-', ' ', $candidate));
        $items = $results['manga_list'] ?? $results['mangas'] ?? $results['results'] ?? $results;

        foreach (is_array($items) ? $items : [] as $item) {
            if (!is_array($item)) {
                continue;
            }

            $endpoint = trim((string) ($item['manga_endpoint'] ?? $item['endpoint'] ?? $item['slug'] ?? ''), '/');
            if ($endpoint === '') {
                continue;
            }

            $resolvedDetail = $this->api->getMangaDetail($endpoint);
            $resolvedManga = $resolvedDetail['manga_detail'] ?? $resolvedDetail;
            $resolvedChapters = is_array($resolvedManga)
                ? ($resolvedManga['chapter_list'] ?? $resolvedManga['chapters'] ?? $resolvedManga['chapter'] ?? [])
                : [];

            foreach (is_array($resolvedChapters) ? $resolvedChapters : [] as $resolvedChapter) {
                $resolvedEndpoint = is_array($resolvedChapter)
                    ? trim((string) ($resolvedChapter['chapter_endpoint'] ?? $resolvedChapter['endpoint'] ?? ''), '/')
                    : '';
                if ($resolvedEndpoint === $currentSlug) {
                    return $endpoint;
                }
            }
        }

        return $candidate;
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

        // The chapter endpoint may provide only one direction. Keep it, but
        // still resolve the missing direction from the manga chapter list.
        // Fallback: derive missing directions from manga detail chapter list.
        $detail = $this->api->getMangaDetail($mangaSlug);
        $manga = $detail['manga_detail'] ?? $detail;
        $chapters = $manga['chapter_list'] ?? $manga['chapters'] ?? $manga['chapter'] ?? [];

        if (!is_array($chapters) || $chapters === []) {
            return [$prevSlug, $nextSlug];
        }

        $currentSlug = trim((string) ($chapter['chapter_endpoint'] ?? $chapterSlug), '/');
        $currentIndex = null;
        $entries = [];

        foreach (array_values($chapters) as $i => $ch) {
            if (!is_array($ch)) {
                continue;
            }

            $slug = trim((string) ($ch['chapter_endpoint'] ?? $ch['endpoint'] ?? ''), '/');
            $title = (string) ($ch['chapter_title'] ?? $ch['title'] ?? $slug);
            $entries[] = [
                'index' => $i,
                'slug' => $slug,
                'number' => $this->extractChapterNumber($title . ' ' . $slug),
            ];
            if ($slug !== '' && $slug === $currentSlug) {
                $currentIndex = $i;
            }
        }

        if ($currentIndex === null) {
            return [$prevSlug, $nextSlug];
        }

        $current = collect($entries)->firstWhere('index', $currentIndex);
        $currentNumber = $current['number'] ?? null;

        // Prefer numeric chapter ordering. This works whether the API returns
        // chapters oldest -> newest or newest -> oldest.
        if ($currentNumber !== null) {
            $numbered = collect($entries)->filter(fn ($entry) => $entry['number'] !== null);

            if ($nextSlug === '') {
                $nextSlug = (string) ($numbered->filter(fn ($entry) => $entry['number'] > $currentNumber)
                    ->sortBy('number')->first()['slug'] ?? '');
            }
            if ($prevSlug === '') {
                $prevSlug = (string) ($numbered->filter(fn ($entry) => $entry['number'] < $currentNumber)
                    ->sortByDesc('number')->first()['slug'] ?? '');
            }
        } else {
            // Last-resort positional fallback for non-numeric chapter labels.
            $position = collect($entries)->search(fn ($entry) => $entry['index'] === $currentIndex);
            if ($nextSlug === '' && isset($entries[$position + 1])) {
                $nextSlug = $entries[$position + 1]['slug'];
            }
            if ($prevSlug === '' && $position !== false && $position > 0) {
                $prevSlug = $entries[$position - 1]['slug'];
            }
        }

        return [$prevSlug, $nextSlug];
    }

    private function extractChapterNumber(string $value): ?float
    {
        if (preg_match('/(?:chapter|chap|ch)[\s._-]*(\d+(?:\.\d+)?)/i', $value, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }
}
