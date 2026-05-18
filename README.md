# MangAlfa 📚

> A premium, mobile-first manga reading website built with **Laravel 12** + **Tailwind CSS**

Dark-mode-only UI, buttery smooth reader, session-based bookmarks & history — powered by the [manga-api](https://github.com/febryardiansyah/manga-api) backend.

---

## ✨ Features

| Feature | Details |
|---|---|
| 🌑 Dark Mode Only | Elegant `#050508` background, purple/blue accents |
| 📱 Mobile-First | Sticky bottom nav, tap-to-hide reader controls |
| 📖 Vertical Reader | Lazy-loaded pages, scroll progress bar, keyboard shortcuts |
| 🔍 Real-time Search | Live AJAX search with 400ms debounce |
| 🔖 Bookmarks | Session-based (+ optional DB migration) |
| 🕐 History | Last 50 chapters tracked automatically |
| 🎭 Genres | Browse & filter by genre |
| ⚡ Caching | 5-minute API response cache via Laravel Cache |

---

## 🚀 Quick Start

### 1 — Clone & Install

```bash
git clone https://github.com/ahmadmct/manga-app.git
cd manga-app
composer install
```

### 2 — Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` — the only required setting:

```env
MANGA_API_BASE_URL=https://mangaapi.arufaa.my.id/api
```

### 3 — Migrate (optional — for DB bookmarks)

```bash
touch database/database.sqlite   # if using SQLite
php artisan migrate
```

Note: if your host uses `SESSION_DRIVER=database`, the app needs a `sessions` table. This repo includes the migration â€” just run `php artisan migrate` on the server after deploy.

### 4 — Serve

```bash
php artisan serve
# → http://127.0.0.1:8000
```

---

## 🗂 Project Structure

```
mangalfa/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── HomeController.php       # Homepage — latest + popular
│   │       ├── MangaController.php      # Detail + browse + genre
│   │       ├── ChapterController.php    # Chapter reader
│   │       ├── SearchController.php     # Search + live-search JSON
│   │       └── BookmarkController.php   # Bookmarks & history (session)
│   └── Services/
│       └── MangaApiService.php          # All API calls + caching
│
├── config/
│   └── manga.php                        # API URL, cache TTL, timeout
│
├── database/
│   └── migrations/
│       └── ..._create_manga_tables.php  # bookmarks + reading_histories
│
├── resources/views/
│   ├── layouts/app.blade.php            # Master layout + bottom nav
│   ├── home.blade.php                   # Homepage
│   ├── manga/
│   │   ├── index.blade.php              # Browse / paginated list
│   │   ├── show.blade.php               # Manga detail + chapter list
│   │   └── genre.blade.php              # Genre filtered list
│   ├── chapter/
│   │   └── show.blade.php               # Vertical reader
│   ├── search/
│   │   └── index.blade.php              # Search + real-time
│   ├── bookmarks/
│   │   └── index.blade.php
│   ├── history/
│   │   └── index.blade.php
│   └── errors/
│       └── 404.blade.php
│
└── routes/
    ├── web.php                          # All routes
    └── console.php                      # Artisan commands
```

---

## 🌐 API Endpoints Used

| Route | API call |
|---|---|
| `/` | `GET /manga/page/1` + `GET /manga/popular/1` + `GET /genres` |
| `/manga` | `GET /manga/page/{page}` |
| `/manga/{slug}` | `GET /manga/detail/{slug}` |
| `/read/{chapterSlug}` | `GET /chapter/{chapterSlug}` |
| `/search?q=...` | `GET /search/{query}` |
| `/genre/{genre}` | `GET /genres/{genre}/{page}` |

All responses are cached for **5 minutes** (configurable via `MANGA_CACHE_TTL`).

---

## 🗺 URL Structure

```
/                                  → Homepage
/manga                             → Browse all (paginated)
/manga/one-piece                   → Manga detail
/read/one-piece-chapter-1-...      → Chapter reader
/search?q=naruto                   → Search results
/genre/action                      → Genre browse
/bookmarks                         → Saved manga
/history                           → Reading history
```

---

## 🎨 Design System

| Token | Value |
|---|---|
| Background | `#050508` |
| Surface | `#0a0a12` / `#111120` |
| Accent | `#7c3aed` (purple) |
| Accent Light | `#a78bfa` |
| Text | `#e2e8f0` / `#94a3b8` |
| Font Display | Syne 800 |
| Font Body | Space Grotesk |

---

## ⌨️ Reader Keyboard Shortcuts

| Key | Action |
|---|---|
| `↓ / PageDown` | Scroll down 80vh |
| `↑ / PageUp` | Scroll up 80vh |
| `H` | Toggle UI controls |
| `F` | Toggle fullscreen |

---

## 🔧 Configuration (`config/manga.php`)

```php
'api_base_url' => env('MANGA_API_BASE_URL', 'https://mangaapi.arufaa.my.id/api'),
'cache_ttl'    => env('MANGA_CACHE_TTL', 300),   // seconds
'timeout'      => env('MANGA_API_TIMEOUT', 15),   // HTTP timeout
```

---

## 🛠 Artisan Commands

```bash
# Clear all cached API responses
php artisan manga:clear-cache
```

---

## 📦 Tech Stack

- **Laravel 12** — routing, controllers, service classes, blade templates, HTTP client, caching
- **Tailwind CSS** (CDN) — utility-first styling, dark mode
- **Vanilla JS** — reader controls, live search, bookmark toggle, lazy loading
- **Intersection Observer API** — lazy image loading in reader
- **Session storage** — bookmarks + reading history (no auth required)

---

## 🚧 Extending to Database Bookmarks

The migration `create_manga_tables.php` creates `bookmarks` and `reading_histories` tables. To use DB storage instead of sessions, update `BookmarkController` to use Eloquent models with `session()->getId()` as the user identifier.

---

## 📄 License

MIT — Free to use, modify, and distribute.
