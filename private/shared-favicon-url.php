<?php
// Resolves absolute favicon URL from current request environment:
// {scheme}://{host}{app_base}/user/admin/site/{site_favicon}
// Uses only site_settings.site_favicon from DB. No fallback icons are applied.
if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once dirname(__DIR__) . '/config.php';
}

$sharedFaviconUrl = '';
try {
    $faviconFile = '';

    $favRes = $conn->query("SELECT `value` FROM site_settings WHERE `key`='site_favicon' LIMIT 1");
    if ($favRes && $favRes->num_rows > 0) {
        $faviconFile = basename((string)($favRes->fetch_assoc()['value'] ?? ''));
    }

    if ($faviconFile !== '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = (string)($_SERVER['HTTP_HOST'] ?? '');

        $appBase = '';
        $docRoot = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $projRoot = realpath(dirname(__DIR__));
        if ($docRoot && $projRoot && str_starts_with($projRoot, $docRoot)) {
            $rel = substr($projRoot, strlen($docRoot));
            $rel = str_replace('\\', '/', $rel);
            $appBase = '/' . trim($rel, '/');
            if ($appBase === '/') {
                $appBase = '';
            }
        }

        if ($host !== '') {
            $sharedFaviconUrl = $scheme . '://' . $host . $appBase . '/user/admin/site/' . rawurlencode($faviconFile);
        }
    }
} catch (Throwable $e) {
    $sharedFaviconUrl = '';
}
