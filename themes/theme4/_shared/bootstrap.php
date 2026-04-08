<?php
include_once __DIR__ . '/../session.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
include_once __DIR__ . '/../config.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    require dirname(__DIR__, 3) . '/config.php';
}

$themeMeta = require __DIR__ . '/theme-meta.php';

$site = [
    'name' => 'Community Trust Bank',
    'addr' => '',
    'phone' => '',
    'email' => '',
    'year' => 'Copyright',
    'login' => 'user',
    'url' => '',
    'image' => 'sc.png',
    'tawk' => '',
];
try {
    $siteRes = $conn->query("SELECT * FROM site ORDER BY id ASC LIMIT 1");
    if ($siteRes && $siteRes->num_rows > 0) {
        $site = array_merge($site, $siteRes->fetch_assoc());
    }
} catch (Throwable $e) {}

$settingMap = [];
try {
    $setRes = $conn->query("SELECT `key`,`value` FROM site_settings");
    if ($setRes) {
        while ($r = $setRes->fetch_assoc()) {
            $settingMap[(string)$r['key']] = (string)$r['value'];
        }
    }
} catch (Throwable $e) {}
try {
    $setRes2 = $conn->query("SELECT setting_key, setting_value FROM site_settings");
    if ($setRes2) {
        while ($r = $setRes2->fetch_assoc()) {
            $k = (string)($r['setting_key'] ?? '');
            if ($k !== '') {
                $settingMap[$k] = (string)($r['setting_value'] ?? '');
            }
        }
    }
} catch (Throwable $e) {}

$frontendScheme = $settingMap['frontend_color_scheme'] ?? 'classic';
$palettes = [
    'classic' => ['bg' => '#f5f7fb', 'surface' => '#ffffff', 'ink' => '#0f172a', 'muted' => '#475569', 'primary' => '#0f4c81', 'primary2' => '#0b3558', 'accent' => '#d4af37', 'line' => '#dbe3ef'],
    'ocean' => ['bg' => '#edf8ff', 'surface' => '#ffffff', 'ink' => '#082f49', 'muted' => '#155e75', 'primary' => '#0e7490', 'primary2' => '#164e63', 'accent' => '#22d3ee', 'line' => '#bae6fd'],
    'forest' => ['bg' => '#eefaf3', 'surface' => '#ffffff', 'ink' => '#052e16', 'muted' => '#166534', 'primary' => '#15803d', 'primary2' => '#14532d', 'accent' => '#84cc16', 'line' => '#bbf7d0'],
    'ember' => ['bg' => '#fff7ed', 'surface' => '#ffffff', 'ink' => '#431407', 'muted' => '#9a3412', 'primary' => '#c2410c', 'primary2' => '#7c2d12', 'accent' => '#fb923c', 'line' => '#fed7aa'],
    'royal' => ['bg' => '#f5f3ff', 'surface' => '#ffffff', 'ink' => '#2e1065', 'muted' => '#5b21b6', 'primary' => '#6d28d9', 'primary2' => '#4c1d95', 'accent' => '#a78bfa', 'line' => '#ddd6fe'],
    'graphite' => ['bg' => '#f8fafc', 'surface' => '#ffffff', 'ink' => '#111827', 'muted' => '#374151', 'primary' => '#1f2937', 'primary2' => '#0f172a', 'accent' => '#94a3b8', 'line' => '#cbd5e1'],
    'sunset' => ['bg' => '#fff1f2', 'surface' => '#ffffff', 'ink' => '#4c0519', 'muted' => '#9f1239', 'primary' => '#be123c', 'primary2' => '#881337', 'accent' => '#fb7185', 'line' => '#fecdd3'],
    'teal-gold' => ['bg' => '#f0fdfa', 'surface' => '#ffffff', 'ink' => '#042f2e', 'muted' => '#0f766e', 'primary' => '#0f766e', 'primary2' => '#134e4a', 'accent' => '#d4af37', 'line' => '#99f6e4'],
    'midnight' => ['bg' => '#0b1020', 'surface' => '#111a32', 'ink' => '#e2e8f0', 'muted' => '#94a3b8', 'primary' => '#2563eb', 'primary2' => '#1d4ed8', 'accent' => '#22d3ee', 'line' => '#334155'],
    'mint' => ['bg' => '#ecfdf5', 'surface' => '#ffffff', 'ink' => '#064e3b', 'muted' => '#047857', 'primary' => '#10b981', 'primary2' => '#059669', 'accent' => '#34d399', 'line' => '#a7f3d0'],
    'sandstone' => ['bg' => '#fef7ed', 'surface' => '#fffaf3', 'ink' => '#7c2d12', 'muted' => '#9a3412', 'primary' => '#c2410c', 'primary2' => '#9a3412', 'accent' => '#f59e0b', 'line' => '#fed7aa'],
    'plum' => ['bg' => '#faf5ff', 'surface' => '#ffffff', 'ink' => '#581c87', 'muted' => '#7e22ce', 'primary' => '#9333ea', 'primary2' => '#6b21a8', 'accent' => '#c084fc', 'line' => '#e9d5ff'],
];

$themeTweaks = [
    'theme2' => ['hero' => 'from-[var(--primary)]/95 via-[var(--primary2)] to-black', 'card' => 'shadow-xl border border-[var(--line)]/70'],
    'theme3' => ['hero' => 'from-black via-slate-900 to-[var(--primary2)]', 'card' => 'shadow-2xl border border-white/10 bg-slate-950/50 text-white backdrop-blur'],
    'theme4' => ['hero' => 'from-[var(--accent)] via-[var(--primary)] to-[var(--primary2)]', 'card' => 'shadow-lg border border-[var(--line)] bg-white/95'],
    'theme5' => ['hero' => 'from-[var(--primary2)] via-[var(--primary)] to-[var(--accent)]', 'card' => 'shadow-2xl border border-[var(--line)] bg-gradient-to-br from-white to-[var(--bg)]'],
];

if (!isset($palettes[$frontendScheme])) {
    $frontendScheme = 'classic';
}
$palette = $palettes[$frontendScheme];
$tweak = $themeTweaks[$themeMeta['key']] ?? $themeTweaks['theme2'];

$navColumns = [
    'Personal' => [
        ['Checking', 'Checking.php'], ['Savings', 'Savings.php'], ['Money Market', 'Money-Market.php'],
        ['Certificates of Deposit', 'Certificates-of-Deposit.php'], ['Debit Cards', 'Debit-Cards.php'], ['Check Orders', 'Check-Orders.php'],
    ],
    'Business' => [
        ['Business Checking', 'Business-Checking.php'], ['Business Savings', 'Business-Savings.php'], ['Business Money Market', 'Business-Money-Market.php'],
        ['Business CDs', 'Business-Certificates-of-Deposit.php'], ['Business Debit Cards', 'Business-Debit-Cards.php'],
    ],
    'Digital' => [
        ['Online Banking', 'Online-Banking.php'], ['Business Online Banking', 'Business-Online-Banking.php'], ['Mobile Banking', 'Mobile-Banking.php'], ['Payments & Receivables', 'Payment-and-Receivables.php'],
    ],
    'About' => [
        ['Company Profile', 'Company-Profile.php'], ['Locations', 'Locations.php'], ['Contact Us', 'Contact-Us.php'], ['News & Press Releases', 'News-and-Press-Releases.php'], ['Security Center', 'Security-Center.php'],
    ],
];

$basePath = rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'))), '/');
if ($basePath === '' || $basePath === '.') {
    $basePath = '';
}
$appBasePath = preg_replace('#/themes/[^/]+$#', '', $basePath);
if (!is_string($appBasePath) || $appBasePath === '.' || $appBasePath === '/') {
    $appBasePath = $appBasePath === '/' ? '' : (string)$appBasePath;
}
$themeAssetBase = $basePath . '/themes/theme1/';

function app_url(string $path = ''): string {
    $base = rtrim((string)($GLOBALS['appBasePath'] ?? ''), '/');
    $clean = ltrim($path, '/');
    if ($clean === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '' : $base) . '/' . $clean;
}

function theme_asset_url(string $path): string {
    $base = $GLOBALS['themeAssetBase'] ?? '/themes/theme1/';
    return $base . ltrim($path, '/');
}

function theme1_render_middle(string $sourcePath): string {
    $conn = $GLOBALS['conn'] ?? null;
    if (!is_file($sourcePath)) {
        return '<section class="max-w-6xl mx-auto px-4 py-20"><h1 class="text-3xl font-bold">Page not found</h1></section>';
    }

    ob_start();
    $prevHandler = set_error_handler(static function ($severity, $message) {
        if (is_string($message) && str_contains($message, 'session_start(): Ignoring session_start() because a session is already active')) {
            return true;
        }
        return false;
    });
    include $sourcePath;
    if ($prevHandler !== null) {
        set_error_handler($prevHandler);
    } else {
        restore_error_handler();
    }
    $html = ob_get_clean();
    if (!is_string($html) || $html === '') {
        return '<section class="max-w-6xl mx-auto px-4 py-20"><h1 class="text-3xl font-bold">No content</h1></section>';
    }

    $middle = '';
    if (preg_match('/<div id="subpage-content"[^>]*>(.*)<div id="contact-stripe"/is', $html, $m)) {
        $middle = $m[1];
    } elseif (preg_match('/<section id="content"[^>]*>(.*)<footer id="footer"/is', $html, $m)) {
        $middle = $m[1];
    } elseif (preg_match('/<\/header>(.*)<footer id="footer"/is', $html, $m)) {
        $middle = $m[1];
    } elseif (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $m)) {
        $middle = $m[1];
    } else {
        $middle = $html;
    }

    $middle = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $middle);

    // Remove legacy fixed image dimensions so responsive CSS can control sizing.
    $middle = preg_replace_callback('/<img\b[^>]*>/i', static function ($match) {
        $tag = $match[0];
        $tag = preg_replace('/\s(?:width|height)\s*=\s*("|\').*?\1/i', '', $tag);
        $tag = preg_replace_callback('/\sstyle\s*=\s*("|\')(.*?)\1/i', static function ($m) {
            $styles = preg_replace('/(^|;)\s*(?:width|max-width|height)\s*:[^;]*/i', '$1', $m[2]);
            $styles = trim((string)preg_replace('/;+/', ';', (string)$styles), " ;");
            if ($styles === '') {
                return '';
            }
            return ' style="' . $styles . '"';
        }, (string)$tag);
        return (string)$tag;
    }, (string)$middle);

    $middle = preg_replace_callback(
        '/\b(src|href|poster)\s*=\s*(["\'])([^"\']+)\2/i',
        static function ($mm) {
            $attr = strtolower($mm[1]);
            $q = $mm[2];
            $url = trim($mm[3]);
            if ($url === '' || preg_match('/^(?:https?:|data:|mailto:|tel:|javascript:|#|\/)/i', $url) || str_starts_with($url, '/themes/') || str_starts_with($url, 'asset.php') || str_starts_with($url, '/asset.php')) {
                return $mm[0];
            }

            if ($attr === 'href' && preg_match('/\.(?:php|html?)(?:$|[?#])/i', $url)) {
                return $mm[0];
            }

            if (!preg_match('/\.(?:png|jpe?g|gif|svg|webp|ico|css|js|woff2?|ttf|otf|eot|map)(?:$|[?#])/i', $url)) {
                return $mm[0];
            }

            return $attr . '=' . $q . theme_asset_url($url) . $q;
        },
        $middle
    );

    $middle = preg_replace_callback(
        '/url\(([^)]+)\)/i',
        static function ($mm) {
            $raw = trim($mm[1], " \t\n\r\0\x0B\"'");
            if ($raw === '' || preg_match('/^(?:https?:|data:|#|\/)/i', $raw)) {
                return 'url(' . $mm[1] . ')';
            }
            return "url('" . theme_asset_url($raw) . "')";
        },
        $middle
    );

    return $middle;
}

$currentPage = basename((string)($_GET['_page'] ?? 'index.php'));
$currentPage = preg_replace('/[^A-Za-z0-9._-]/', '', $currentPage);
if ($currentPage === '') {
    $currentPage = 'index.php';
}
$pageTitle = preg_replace('/\.php$/', '', str_replace(['-', '_'], ' ', $currentPage));
if ($pageTitle === 'index') {
    $pageTitle = 'Home';
}

function nav_is_active(string $href, string $currentPage): bool {
    $target = basename(strtok($href, '#'));
    return strcasecmp($target, $currentPage) === 0;
}

function nav_group_active(array $links, string $currentPage): bool {
    foreach ($links as $link) {
        if (isset($link[1]) && nav_is_active((string)$link[1], $currentPage)) {
            return true;
        }
    }
    return false;
}
