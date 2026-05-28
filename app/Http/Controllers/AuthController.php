<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        if (!Auth::attempt([$field => $credentials['login'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => 'Nama/email atau password tidak cocok.',
            ]);
        }

        $request->session()->regenerate();
        $this->attachSessionHistoryToAccount($request);
        $this->attachSessionBookmarksToAccount($request);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function attachSessionHistoryToAccount(Request $request): void
    {
        $history = $request->session()->get('reading_history', []);

        foreach ($history as $item) {
            if (!is_array($item) || empty($item['chapter_slug']) || empty($item['manga_slug'])) {
                continue;
            }

            ReadingHistory::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'chapter_slug' => trim((string) $item['chapter_slug'], '/'),
                ],
                [
                    'manga_slug' => trim((string) $item['manga_slug'], '/'),
                    'manga_title' => $item['manga_title'] ?? null,
                    'manga_thumb' => $item['thumb'] ?? null,
                    'chapter_title' => $item['chapter_title'] ?? null,
                ]
            );
        }
    }

    private function attachSessionBookmarksToAccount(Request $request): void
    {
        $bookmarks = $request->session()->get('bookmarks', []);

        foreach ($bookmarks as $item) {
            if (!is_array($item) || empty($item['slug'])) {
                continue;
            }

            Bookmark::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'manga_slug' => trim((string) $item['slug'], '/'),
                ],
                [
                    'manga_title' => $item['title'] ?? 'Unknown',
                    'manga_thumb' => $item['thumb'] ?? null,
                    'manga_type' => $item['type'] ?? 'Manga',
                ]
            );
        }
    }
}
