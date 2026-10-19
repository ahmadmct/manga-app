<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImageProxyController extends Controller
{
    private array $refererMap = [
        'img.komiku.org'    => 'https://komiku.org/',
        'img.mangkomic.me'  => 'https://mangkomic.me/',
    ];

    public function proxy(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid URL');
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $isKomikuImageHost = preg_match('/^image\d+\.komiku\.to$/', $host) === 1;
        $isLegacyAllowedHost = isset($this->refererMap[$host]);

        if (($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && $parts['port'] !== 443)
            || (!$isKomikuImageHost && !$isLegacyAllowedHost)) {
            abort(403, 'Host not allowed');
        }

        // The API rotates pages through image2, image3, image4, etc. In
        // practice some of those aliases time out while image2 serves the
        // same page paths, so normalize the rotating aliases to the stable one.
        if ($isKomikuImageHost) {
            $url = preg_replace('/^https:\/\/image\d+\.komiku\.to(?=\/)/i', 'https://image2.komiku.to', $url);
            $host = 'image2.komiku.to';
        }

        $referer = $this->refererMap[$host] ?? 'https://komiku.to/';
        $response = Http::withHeaders([
            'Referer'         => $referer,
            'User-Agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'Accept'          => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
            'Accept-Encoding' => 'identity',
        ])->retry(3, 300)->timeout(20)->get($url);

        if (!$response->successful()) {
            abort(502, 'Image source unavailable');
        }

        $contentType = strtolower(trim(explode(';', $response->header('Content-Type') ?? '')[0]));
        if (!str_starts_with($contentType, 'image/')) {
            abort(502, 'Image source returned a non-image response');
        }

        return response($response->body(), 200, [
            'Content-Type'  => $contentType,
            'Cache-Control' => 'public, max-age=3600',
            'X-Proxied'     => '1',
        ]);
    }
}
