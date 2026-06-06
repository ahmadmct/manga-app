<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ImageProxyController extends Controller
{
    // Domain → referer yang dibutuhkan
    private array $refererMap = [
        'img.komiku.org'    => 'https://komiku.org/',
        'img.mangkomic.me'  => 'https://mangkomic.me/',
        // tambahkan domain lain di sini
    ];

    public function proxy(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid URL');
        }

        $host = parse_url($url, PHP_URL_HOST);
        $allowed = array_keys($this->refererMap);
        if (!in_array($host, $allowed, true)) {
            abort(403, 'Host not allowed');
        }

        $cacheKey = 'img_proxy_' . md5($url);

        // Cache hanya metadata, bukan response object
        $cached = Cache::get($cacheKey);

        if (!$cached) {
            $referer = $this->refererMap[$host] ?? 'https://' . $host . '/';

            $response = Http::withHeaders([
                'Referer'         => $referer,
                'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept'          => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                'Accept-Encoding' => 'identity', // <-- hindari compressed response
                'Connection'      => 'keep-alive',
            ])->timeout(15)->get($url);

            if (!$response->successful()) {
                abort($response->status(), 'Image fetch failed');
            }

            $cached = [
                'content_type' => $response->header('Content-Type') ?? 'image/jpeg',
                'body'         => base64_encode($response->body()), // encode dulu
            ];

            Cache::put($cacheKey, $cached, now()->addHours(6));
        }

        return response(base64_decode($cached['body']), 200, [
            'Content-Type'  => $cached['content_type'],
            'Cache-Control' => 'public, max-age=21600',
            'X-Proxied'     => '1',
        ]);
    }
}
