<?php
/**
 * Proxy flag images from flagcdn.com with caching.
 * Usage: flag-preview.php?code=US  (ISO 3166-1 alpha-2)
 */
$code = strtolower(trim($_GET['code'] ?? ''));
if (!preg_match('/^[a-z]{2}$/', $code)) {
    http_response_code(400);
    exit();
}

// Look for a locally downloaded SVG first
$localSvg = __DIR__ . '/user/assets/flags/' . $code . '.svg';
if (file_exists($localSvg)) {
    header('Content-Type: image/svg+xml');
    header('Cache-Control: public, max-age=604800');
    readfile($localSvg);
    exit();
}

// Fall through: redirect to flagcdn
header('Location: https://flagcdn.com/w40/' . $code . '.png');
exit();
