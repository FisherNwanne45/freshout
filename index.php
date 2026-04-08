<?php
/**
 * Theme Front Controller
 *
 * Reads the active theme from the database and dispatches every
 * frontend page request to the correct theme folder.
 *
 * Infrastructure files (config.php, session.php, submit.php) remain at
 * the project root and are NOT routed through here.
 *
 * Routing is handled by .htaccess:
 *   - PHP page requests  → index.php?_page=Filename.php
 *   - Static assets      → rewritten directly to themes/<active>/…
 */

require_once __DIR__ . '/config.php';

// ── Active theme resolution ──────────────────────────────────────────
$theme = 'theme1'; // safe fallback

$themeRow = $conn->query(
    "SELECT `value` FROM site_settings WHERE `key` = 'theme' LIMIT 1"
);
if ($themeRow && $themeRow->num_rows > 0) {
    $candidate = $themeRow->fetch_assoc()['value'];
    // Only alphanumeric, hyphens and underscores are allowed.
    if (preg_match('/^[a-zA-Z0-9_-]+$/', $candidate)) {
        $theme = $candidate;
    }
}

define('ACTIVE_THEME', $theme);
define('THEMES_DIR', __DIR__ . '/themes/');

// ── Page resolution ──────────────────────────────────────────────────
$requested = isset($_GET['_page']) ? $_GET['_page'] : 'index.php';

// Strip any directory component and query string — only a plain filename
// is ever valid here.
$requested = basename(strtok($requested, '?'));

// Ensure .php extension.
if (!str_ends_with($requested, '.php')) {
    $requested .= '.php';
}

// Whitelist: filename characters only (letters, digits, hyphens, underscores, dots).
$requested = preg_replace('/[^A-Za-z0-9._-]/', '', $requested);

// Build and canonicalise the path.
$themeDir  = THEMES_DIR . $theme . DIRECTORY_SEPARATOR;
$pagePath  = $themeDir . $requested;
$realPage  = realpath($pagePath);
$realDir   = realpath($themeDir);

if (
    $realPage !== false &&
    $realDir  !== false &&
    strncmp($realPage, $realDir . DIRECTORY_SEPARATOR, strlen($realDir) + 1) === 0 &&
    is_file($realPage)
) {
    include $realPage;
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>404</title></head>'
       . '<body><h1>404 &mdash; Page Not Found</h1></body></html>';
}
