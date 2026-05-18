<?php

namespace App\Http\Controllers;

use App\Services\MangaApiService;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function __construct(protected MangaApiService $api) {}

    public function index()
    {
        $bookmarks = session('bookmarks', []);
        $history = session('reading_history', []);

        // Enrich bookmarks with "unread count" from last read -> latest chapter.
        $bookmarks = array_values(array_map(function ($bookmark) use ($history) {
            if (!is_array($bookmark)) {
                return $bookmark;
            }

            $slug = (string) ($bookmark['slug'] ?? '');
            if ($slug === '') {
                return $bookmark;
            }

            $lastRead = null;
            foreach ($history as $h) {
                if (!is_array($h)) {
                    continue;
                }
                if (($h['manga_slug'] ?? '') === $slug) {
                    $lastRead = $h;
                    break; // history is newest-first
                }
            }

            if (!$lastRead || empty($lastRead['chapter_slug'])) {
                return $bookmark;
            }

            $detail = $this->api->getMangaDetail($slug);
            if (isset($detail['error'])) {
                return $bookmark;
            }

            $manga = $detail['manga_detail'] ?? $detail;
            $chapters = is_array($manga)
                ? ($manga['chapter_list'] ?? $manga['chapters'] ?? $manga['chapter'] ?? [])
                : [];

            if (!is_array($chapters) || $chapters === []) {
                return $bookmark;
            }

            // API chapter list is commonly newest -> oldest.
            $latest = $chapters[0] ?? null;
            $latestSlug = is_array($latest) ? (string) ($latest['chapter_endpoint'] ?? $latest['endpoint'] ?? '') : '';
            $latestTitle = is_array($latest) ? (string) ($latest['chapter_title'] ?? $latest['title'] ?? '') : '';

            $lastReadSlug = trim((string) $lastRead['chapter_slug'], '/');
            $idx = null;

            foreach (array_values($chapters) as $i => $ch) {
                if (!is_array($ch)) {
                    continue;
                }
                $chSlug = trim((string) ($ch['chapter_endpoint'] ?? $ch['endpoint'] ?? ''), '/');
                if ($chSlug !== '' && $chSlug === $lastReadSlug) {
                    $idx = $i;
                    break;
                }
            }

            if ($idx === null) {
                return $bookmark;
            }

            $unread = max(0, (int) $idx);
            if ($unread <= 0) {
                return $bookmark;
            }

            $bookmark['unread_count'] = $unread;
            if ($latestSlug !== '') {
                $bookmark['latest_chapter_slug'] = trim($latestSlug, '/');
            }
            if ($latestTitle !== '') {
                $bookmark['latest_chapter_title'] = $latestTitle;
            }

            return $bookmark;
        }, $bookmarks));

        return view('bookmarks.index', compact('bookmarks'));
    }

    public function toggle(Request $request)
    {
        $slug = $request->input('slug');
        $title = $request->input('title');
        $thumb = $request->input('thumb');
        $type = $request->input('type', 'Manga');

        $bookmarks = session('bookmarks', []);
        $existing = array_search($slug, array_column($bookmarks, 'slug'));

        if ($existing !== false) {
            // Remove bookmark
            array_splice($bookmarks, $existing, 1);
            $bookmarked = false;
        } else {
            // Add bookmark
            array_unshift($bookmarks, [
                'slug' => $slug,
                'title' => $title,
                'thumb' => $thumb,
                'type' => $type,
                'bookmarked_at' => now()->toDateTimeString(),
            ]);
            $bookmarked = true;
        }

        session(['bookmarks' => $bookmarks]);

        return response()->json([
            'bookmarked' => $bookmarked,
            'count' => count($bookmarks),
        ]);
    }

    public function addHistory(Request $request)
    {
        $data = $request->only(['chapter_slug', 'chapter_title', 'manga_slug', 'manga_title', 'thumb']);
        $history = session('reading_history', []);

        $history = array_filter($history, fn($h) => ($h['chapter_slug'] ?? '') !== $data['chapter_slug']);
        array_unshift($history, array_merge($data, ['read_at' => now()->toDateTimeString()]));

        session(['reading_history' => array_slice(array_values($history), 0, 50)]);

        return response()->json(['success' => true]);
    }

    public function history()
    {
        $history = session('reading_history', []);
        return view('history.index', compact('history'));
    }
}
