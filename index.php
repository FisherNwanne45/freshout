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

/** @var mysqli $conn */
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

// FIX: Remove query string but DO NOT use basename(), so folder names are preserved.
$requested = strtok($requested, '?');

// Ensure .php extension.
if (!str_ends_with($requested, '.php')) {
    $requested .= '.php';
}

// FIX: Whitelist now includes the forward slash '/' to allow nested folders (e.g., about/index.php)
// It also strips out empty dots like '..' to prevent path traversal vulnerability attacks.
$requested = str_replace(array('..', '\\'), '', $requested);
$requested = preg_replace('/[^A-Za-z0-9._\/-]/', '', $requested);
$requested = ltrim($requested, '/');

// Build and canonicalise the path.
// Note: We use '/' instead of DIRECTORY_SEPARATOR here to guarantee matching in realpath comparisons on Windows.
$themeDir  = THEMES_DIR . $theme . '/';
$pagePath  = $themeDir . $requested;
$realPage  = realpath($pagePath);
$realDir   = realpath($themeDir);

// Verify that the requested file exists and lives strictly inside the active theme directory
if (
    $realPage !== false &&
    $realDir  !== false &&
    strpos($realPage, $realDir) === 0 &&
    is_file($realPage)
) {
    // Make relative includes inside theme files resolve against that subfolder directory if it has one.
    $oldCwd = getcwd();
    $targetDir = dirname($realPage);
    chdir($targetDir);

    include $realPage;

    if ($oldCwd !== false) {
        chdir($oldCwd);
    }
} else {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>404</title></head>'
        . '<body><h1>404 &mdash; Page Not Found</h1></body></html>';
}
