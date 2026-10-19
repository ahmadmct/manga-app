<?php

if (!function_exists('needsProxy')) {
    function needsProxy(string $url): bool
    {
        $parts = parse_url($url);
        if (!is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https') {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');
        $proxiedHosts = [
            'img.komiku.org',
            'img.mangkomic.me',
        ];

        return in_array($host, $proxiedHosts, true)
            || preg_match('/^image\d+\.komiku\.to$/', $host) === 1;
    }
}

?>
