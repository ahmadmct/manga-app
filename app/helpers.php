<?php

if (!function_exists('needsProxy')) {
    function needsProxy(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $proxiedHosts = [
            'img.komiku.org',
            'img.mangkomic.me',
            // tambah host lain
        ];
        return in_array($host, $proxiedHosts, true);
    }
}

?>
