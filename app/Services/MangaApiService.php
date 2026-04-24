<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MangaApiService
{
    protected string $baseUrl;
    protected int $cacheTtl = 300; // seconds
    protected int $timeout = 15; // seconds

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('manga.api_base_url', 'http://127.0.0.1:3000/api'), '/');
        $this->cacheTtl = (int) config('manga.cache_ttl', 300);
        $this->timeout = (int) config('manga.timeout', 15);
    }

    /**
     * Get latest manga updates (paginated)
     */
    public function getLatestManga(int $page = 1): array
    {
        return $this->cachedGet("v2_latest_{$page}", function () use ($page) {
            $data = $this->get("/manga/page/{$page}");
            return $this->normalizeMangaListResponse($data);
        });
    }

    /**
     * Get latest manga updates with a custom page size (virtual pagination).
     * The upstream API uses a fixed page size, so we fetch multiple API pages and slice.
     */
    public function getLatestMangaPerPage(int $page = 1, int $perPage = 24): array
    {
        return $this->virtualPaginateMangaList(
            'latest',
            fn (int $apiPage) => $this->getLatestManga($apiPage),
            $page,
            $perPage
        );
    }

    /**
     * Get popular manga (paginated)
     */
    public function getPopularManga(int $page = 1): array
    {
        return $this->cachedGet("v2_popular_{$page}", function () use ($page) {
            $data = $this->get("/manga/popular/{$page}");
            return $this->normalizeMangaListResponse($data);
        });
    }

    /**
     * Get recommended manga (fallbacks to popular list)
     */
    public function getRecommendedManga(int $page = 1): array
    {
        return $this->cachedGet("v2_recommended_{$page}", function () use ($page) {
            $data = $this->get("/manga/popular/{$page}");
            return $this->normalizeMangaListResponse($data);
        });
    }

    public function getPopularMangaPerPage(int $page = 1, int $perPage = 24): array
    {
        return $this->virtualPaginateMangaList(
            'popular',
            fn (int $apiPage) => $this->getPopularManga($apiPage),
            $page,
            $perPage
        );
    }

    public function getRecommendedMangaPerPage(int $page = 1, int $perPage = 24): array
    {
        return $this->virtualPaginateMangaList(
            'recommended',
            fn (int $apiPage) => $this->getRecommendedManga($apiPage),
            $page,
            $perPage
        );
    }

    public function getRecomendedManga(int $page = 1): array
    {
        return $this->cachedGet("v2_recommended_{$page}", function () use ($page) {
            $data = $this->get("/recommended/{$page}");
            return $this->normalizeMangaListResponse($data);
        });
    }

    /**
     * Get manga detail by slug
     */
    public function getMangaDetail(string $slug): array
    {
        $slug = $this->cleanSlug($slug);

        return $this->cachedGet("v2_detail_{$slug}", function () use ($slug) {
            $data = $this->get("/manga/detail/{$slug}");
            return $this->normalizeMangaDetailResponse($data);
        });
    }

    /**
     * Get chapter pages by chapter slug
     */
    public function getChapter(string $chapterSlug): array
    {
        $chapterSlug = $this->cleanSlug($chapterSlug);

        return $this->cachedGet("v2_chapter_{$chapterSlug}", function () use ($chapterSlug) {
            $data = $this->get("/chapter/{$chapterSlug}");
            return $this->normalizeChapterResponse($data);
        });
    }

    /**
     * Search manga by query
     */
    public function searchManga(string $query): array
    {
        $query = trim($query);

        return $this->cachedGet("v2_search_{$query}", function () use ($query) {
            // This API expects `q` as a query parameter: `/search?q=naruto`
            $data = $this->get('/search', ['q' => $query]);
            return $this->normalizeMangaListResponse($data);
        }, 60); // shorter cache for search
    }

    /**
     * Get all genres
     */
    public function getGenres(): array
    {
        return $this->cachedGet('v2_genres', function () {
            $data = $this->get('/genres');
            return $this->normalizeGenresResponse($data);
        }, 3600); // cache genres for 1 hour
    }

    /**
     * Get manga by genre
     */
    public function getMangaByGenre(string $genre, int $page = 1): array
    {
        $genre = $this->cleanSlug($genre);

        return $this->cachedGet("v2_genre_{$genre}_{$page}", function () use ($genre, $page) {
            $data = $this->get("/genres/{$genre}/{$page}");
            return $this->normalizeMangaListResponse($data);
        });
    }

    public function getMangaByGenrePerPage(string $genre, int $page = 1, int $perPage = 24): array
    {
        $genre = $this->cleanSlug($genre);

        return $this->virtualPaginateMangaList(
            "genre_{$genre}",
            fn (int $apiPage) => $this->getMangaByGenre($genre, $apiPage),
            $page,
            $perPage
        );
    }

    /**
     * Make a GET request to the API
     */
    protected function get(string $endpoint, array $queryParams = []): array
    {
        try {
            $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'MangAlfa/1.0',
                ])
                ->get($url, $queryParams);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            return ['error' => 'API returned status ' . $response->status()];
        } catch (\Exception $e) {
            \Log::error("MangaAPI error for {$endpoint}: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Cache wrapper
     */
    protected function cachedGet(string $key, callable $callback, int $ttl = null): array
    {
        $ttl = $ttl ?? $this->cacheTtl;
        $cacheKey = 'manga_api_' . md5($key);

        $data = Cache::get($cacheKey);
        if ($data !== null) {
            return $data;
        }

        $data = $callback();
        Cache::put($cacheKey, $data, $ttl);

        return $data;
    }

    protected function cleanSlug(string $slug): string
    {
        $slug = trim($slug);
        $slug = trim($slug, '/');
        return $slug;
    }

    protected function normalizeMangaListResponse(array $data): array
    {
        if (!empty($data['manga_list']) && is_array($data['manga_list'])) {
            $data['manga_list'] = $this->normalizeMangaList($data['manga_list']);
        }

        return $data;
    }

    protected function virtualPaginateMangaList(string $keyPrefix, callable $fetchApiPage, int $page, int $perPage): array
    {
        $page = max(1, (int) $page);
        $perPage = $this->clampPerPage($perPage);

        $apiPageSize = (int) $this->cachedValue("v2_pagesize_{$keyPrefix}", function () use ($fetchApiPage) {
            $first = $fetchApiPage(1);
            $list = $first['manga_list'] ?? [];
            $count = is_array($list) ? count($list) : 0;
            return max(1, $count);
        }, 3600);

        $startIndex = ($page - 1) * $perPage;
        $apiStartPage = intdiv($startIndex, $apiPageSize) + 1;
        $offset = $startIndex % $apiPageSize;

        $items = [];
        $apiPage = $apiStartPage;
        $safety = 0;

        while (count($items) < $perPage && $safety < 20) {
            $safety++;
            $data = $fetchApiPage($apiPage);
            $list = $data['manga_list'] ?? [];
            if (!is_array($list) || count($list) === 0) {
                break;
            }

            $slice = $apiPage === $apiStartPage ? array_slice($list, $offset) : $list;
            $need = $perPage - count($items);
            $items = array_merge($items, array_slice($slice, 0, $need));

            $apiPage++;
            $offset = 0;
        }

        return [
            'manga_list' => $items,
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    protected function cachedValue(string $key, callable $callback, int $ttl = null)
    {
        $ttl = $ttl ?? $this->cacheTtl;
        $cacheKey = 'manga_api_val_' . md5($key);
        return Cache::remember($cacheKey, $ttl, $callback);
    }

    protected function clampPerPage(int $perPage): int
    {
        $allowed = [6, 12, 24, 36, 48, 60];
        if (in_array($perPage, $allowed, true)) {
            return $perPage;
        }
        return 24;
    }

    protected function normalizeMangaList(array $list): array
    {
        return array_values(array_map(function ($item) {
            if (!is_array($item)) {
                return $item;
            }

            if (isset($item['endpoint']) && is_string($item['endpoint'])) {
                $item['endpoint'] = $this->cleanSlug($item['endpoint']);
            }

            if (isset($item['manga_endpoint']) && is_string($item['manga_endpoint'])) {
                $item['manga_endpoint'] = $this->cleanSlug($item['manga_endpoint']);
            }

            return $item;
        }, $list));
    }

    protected function normalizeMangaDetailResponse(array $data): array
    {
        if (isset($data['error'])) {
            return $data;
        }

        // Some API variants return raw detail object (no wrapper)
        $detail = $data['manga_detail'] ?? $data;

        if (is_array($detail)) {
            if (isset($detail['manga_endpoint']) && is_string($detail['manga_endpoint'])) {
                $detail['manga_endpoint'] = $this->cleanSlug($detail['manga_endpoint']);
            }

            // Fallbacks for backends that return empty/garbled fields
            if (empty($detail['title']) && !empty($detail['manga_endpoint']) && is_string($detail['manga_endpoint'])) {
                $detail['title'] = ucwords(str_replace('-', ' ', $detail['manga_endpoint']));
            }

            if (isset($detail['status']) && is_string($detail['status'])) {
                // Some scrapers return a multiline genre list in `status` (not a real status badge).
                if (str_contains($detail['status'], "\n")) {
                    unset($detail['status']);
                }
            }

            if (!empty($detail['author']) && is_string($detail['author']) && !empty($detail['genre_list']) && is_array($detail['genre_list'])) {
                $genreNames = array_map(
                    fn ($g) => is_array($g) ? ($g['genre_name'] ?? $g['name'] ?? null) : $g,
                    $detail['genre_list']
                );
                $genreNames = array_filter($genreNames, fn ($v) => is_string($v) && $v !== '');
                if (in_array($detail['author'], $genreNames, true)) {
                    unset($detail['author']);
                }
            }

            // Chapters key differs across backends
            $chapters = $detail['chapter_list'] ?? $detail['chapters'] ?? $detail['chapter'] ?? [];
            if (is_array($chapters)) {
                $detail['chapter_list'] = $this->normalizeChapterList($chapters);
            }
        }

        $data['manga_detail'] = $detail;
        return $data;
    }

    protected function normalizeChapterList(array $chapters): array
    {
        return array_values(array_map(function ($chapter) {
            if (!is_array($chapter)) {
                return $chapter;
            }

            foreach (['chapter_endpoint', 'endpoint'] as $key) {
                if (isset($chapter[$key]) && is_string($chapter[$key])) {
                    $chapter[$key] = $this->cleanSlug($chapter[$key]);
                }
            }

            return $chapter;
        }, $chapters));
    }

    protected function normalizeChapterResponse(array $data): array
    {
        if (isset($data['error'])) {
            return $data;
        }

        $chapter = $data['chapter'] ?? $data;
        if (is_array($chapter) && isset($chapter['chapter_endpoint']) && is_string($chapter['chapter_endpoint'])) {
            $chapter['chapter_endpoint'] = $this->cleanSlug($chapter['chapter_endpoint']);
        }

        $data['chapter'] = $chapter;
        return $data;
    }

    protected function normalizeGenresResponse(array $data): array
    {
        if (isset($data['error'])) {
            return $data;
        }

        $genres = $data['genres'] ?? $data['list_genre'] ?? [];
        if (!is_array($genres)) {
            $genres = [];
        }

        $data['genres'] = array_values(array_map(function ($genre) {
            if (!is_array($genre)) {
                return $genre;
            }

            $genreId = $genre['genre_id'] ?? $genre['endpoint'] ?? $genre['id'] ?? null;
            $genreName = $genre['genre_name'] ?? $genre['name'] ?? null;

            if (is_string($genreId)) {
                $genreId = $this->cleanSlug($genreId);
            }

            if ($genreName !== null) {
                $genre['name'] = $genreName;
                $genre['genre_name'] = $genreName;
            }

            if ($genreId !== null) {
                $genre['genre_id'] = $genreId;
            }

            return $genre;
        }, $genres));

        return $data;
    }
}
