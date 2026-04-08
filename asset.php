<?php
require_once __DIR__ . '/config.php';

// Dynamic site favicon endpoint (admin-controlled)
if (isset($_GET['type']) && (string)$_GET['type'] === 'favicon') {
    $faviconFile = '';
    try {
        $favRes = $conn->query("SELECT `value` FROM site_settings WHERE `key`='site_favicon' LIMIT 1");
        if ($favRes && $favRes->num_rows > 0) {
            $faviconFile = basename((string)($favRes->fetch_assoc()['value'] ?? ''));
        }
    } catch (Throwable $e) {
    }

    if ($faviconFile === '') {
        http_response_code(404);
        exit('Favicon not configured');
    }

    $resolved = __DIR__ . '/user/admin/site/' . $faviconFile;
    if (!is_file($resolved)) {
        http_response_code(404);
        exit('Favicon file missing');
    }

    $ext = strtolower(pathinfo($resolved, PATHINFO_EXTENSION));
    $mimeMap = [
        'png' => 'image/png',
        'ico' => 'image/x-icon',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
    ];

    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: ' . ($mimeMap[$ext] ?? 'application/octet-stream'));
    readfile($resolved);
    exit;
}

$theme = 'theme1';
$themeRow = $conn->query("SELECT `value` FROM site_settings WHERE `key`='theme' LIMIT 1");
if ($themeRow && $themeRow->num_rows > 0) {
    $candidate = (string)$themeRow->fetch_assoc()['value'];
    if (preg_match('/^[a-zA-Z0-9_-]+$/', $candidate)) {
        $theme = $candidate;
    }
}

$rawPath = (string)($_GET['path'] ?? '');
$rawPath = ltrim($rawPath, '/');

if ($rawPath === '' || strpos($rawPath, '..') !== false || strpos($rawPath, "\0") !== false) {
    http_response_code(400);
    exit('Bad request');
}

$allowedPrefixes = [
    'css/',
    'js/',
    'img/',
    'fonts/',
    'assets/',
    'images/',
    'js.locatorsearch.com/',
    'maxcdn.bootstrapcdn.com/',
    'static.ctctcdn.com/',
    'www.google-analytics.com/',
];

$allowed = false;
foreach ($allowedPrefixes as $prefix) {
    if (str_starts_with($rawPath, $prefix)) {
        $allowed = true;
        break;
    }
}
if (!$allowed) {
    http_response_code(403);
    exit('Forbidden');
}

$themeBase = __DIR__ . '/themes/' . $theme . '/';
$candidatePath = realpath($themeBase . $rawPath);
$themeRoot = realpath($themeBase);

if ($candidatePath === false || $themeRoot === false || !str_starts_with($candidatePath, $themeRoot . DIRECTORY_SEPARATOR) || !is_file($candidatePath)) {
    // Fallback to theme1 to keep legacy references resilient.
    $fallbackBase = __DIR__ . '/themes/theme1/';
    $candidatePath = realpath($fallbackBase . $rawPath);
    $fallbackRoot = realpath($fallbackBase);
    if ($candidatePath === false || $fallbackRoot === false || !str_starts_with($candidatePath, $fallbackRoot . DIRECTORY_SEPARATOR) || !is_file($candidatePath)) {
        http_response_code(404);
        exit('Not found');
    }
}

$ext = strtolower(pathinfo($candidatePath, PATHINFO_EXTENSION));
$mimeMap = [
    'css' => 'text/css; charset=UTF-8',
    'js' => 'application/javascript; charset=UTF-8',
    'json' => 'application/json; charset=UTF-8',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'webp' => 'image/webp',
    'ico' => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf' => 'font/ttf',
    'otf' => 'font/otf',
    'eot' => 'application/vnd.ms-fontobject',
    'map' => 'application/json; charset=UTF-8',
    'txt' => 'text/plain; charset=UTF-8',
];

header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=3600');
header('Content-Type: ' . ($mimeMap[$ext] ?? 'application/octet-stream'));
readfile($candidatePath);
