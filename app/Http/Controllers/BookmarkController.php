<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\ReadingHistory;
use App\Services\MangaApiService;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function __construct(protected MangaApiService $api) {}

    public function index()
    {
        $bookmarks = $this->bookmarkItems();
        $history = $this->historyItems();

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

        if (auth()->check()) {
            $bookmark = Bookmark::where('user_id', auth()->id())
                ->where('manga_slug', $slug)
                ->first();

            if ($bookmark) {
                $bookmark->delete();
                $bookmarked = false;
            } else {
                Bookmark::create([
                    'user_id' => auth()->id(),
                    'manga_slug' => $slug,
                    'manga_title' => $title,
                    'manga_thumb' => $thumb,
                    'manga_type' => $type,
                ]);
                $bookmarked = true;
            }

            return response()->json([
                'bookmarked' => $bookmarked,
                'count' => Bookmark::where('user_id', auth()->id())->count(),
            ]);
        }

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
        if ($request->boolean('clear_all')) {
            if (auth()->check()) {
                ReadingHistory::where('user_id', auth()->id())->delete();
            }

            session()->forget('reading_history');

            return response()->json(['success' => true]);
        }

        $data = $request->only(['chapter_slug', 'chapter_title', 'manga_slug', 'manga_title', 'thumb']);
        $history = session('reading_history', []);

        $history = array_filter($history, fn($h) => ($h['chapter_slug'] ?? '') !== $data['chapter_slug']);
        array_unshift($history, array_merge($data, ['read_at' => now()->toDateTimeString()]));

        session(['reading_history' => array_slice(array_values($history), 0, 50)]);

        if (auth()->check()) {
            ReadingHistory::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'chapter_slug' => trim((string) ($data['chapter_slug'] ?? ''), '/'),
                ],
                [
                    'manga_slug' => trim((string) ($data['manga_slug'] ?? ''), '/'),
                    'manga_title' => $data['manga_title'] ?? null,
                    'manga_thumb' => $data['thumb'] ?? null,
                    'chapter_title' => $data['chapter_title'] ?? null,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function history()
    {
        $history = $this->historyItems();
        return view('history.index', compact('history'));
    }

    private function historyItems(): array
    {
        if (!auth()->check()) {
            return session('reading_history', []);
        }

        return ReadingHistory::where('user_id', auth()->id())
            ->latest('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (ReadingHistory $history) => [
                'chapter_slug' => $history->chapter_slug,
                'chapter_title' => $history->chapter_title,
                'manga_slug' => $history->manga_slug,
                'manga_title' => $history->manga_title,
                'thumb' => $history->manga_thumb,
                'read_at' => $history->updated_at?->toDateTimeString() ?? now()->toDateTimeString(),
            ])
            ->all();
    }

    private function bookmarkItems(): array
    {
        if (!auth()->check()) {
            return session('bookmarks', []);
        }

        return Bookmark::where('user_id', auth()->id())
            ->latest('updated_at')
            ->get()
            ->map(fn (Bookmark $bookmark) => [
                'slug' => $bookmark->manga_slug,
                'title' => $bookmark->manga_title,
                'thumb' => $bookmark->manga_thumb,
                'type' => $bookmark->manga_type,
                'bookmarked_at' => $bookmark->created_at?->toDateTimeString() ?? now()->toDateTimeString(),
            ])
            ->all();
    }
}
