<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index()
    {
        $bookmarks = session('bookmarks', []);
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
