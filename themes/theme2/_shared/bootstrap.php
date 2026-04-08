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
} catch (Throwable $e) {
}

$settingMap = [];
try {
    $setRes = $conn->query("SELECT `key`,`value` FROM site_settings");
    if ($setRes) {
        while ($r = $setRes->fetch_assoc()) {
            $settingMap[(string)$r['key']] = (string)$r['value'];
        }
    }
} catch (Throwable $e) {
}
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
} catch (Throwable $e) {
}

function theme_setting(string $key, string $default = ''): string {
    $map = $GLOBALS['settingMap'] ?? [];
    if (is_array($map) && array_key_exists($key, $map)) {
        return (string)$map[$key];
    }
    return $default;
}

$frontendScheme = $settingMap['frontend_color_scheme'] ?? 'classic';
$palettes = [
    'classic' => ['bg' => '#f3f6fb', 'surface' => '#ffffff', 'ink' => '#102033', 'muted' => '#526274', 'primary' => '#173f6d', 'primary2' => '#0d2847', 'accent' => '#c6a65a', 'line' => '#d7e0ec'],
    'ocean' => ['bg' => '#eef7fb', 'surface' => '#ffffff', 'ink' => '#0b2f40', 'muted' => '#28566a', 'primary' => '#0f5d78', 'primary2' => '#0a4357', 'accent' => '#58a6c2', 'line' => '#c8e0eb'],
    'forest' => ['bg' => '#eff6f0', 'surface' => '#ffffff', 'ink' => '#183127', 'muted' => '#4e675c', 'primary' => '#285642', 'primary2' => '#19382a', 'accent' => '#9aab75', 'line' => '#d4dfd4'],
    'ember' => ['bg' => '#fbf5f0', 'surface' => '#ffffff', 'ink' => '#3f2214', 'muted' => '#77503a', 'primary' => '#9b4f2f', 'primary2' => '#69311c', 'accent' => '#d69962', 'line' => '#ecd9cb'],
    'royal' => ['bg' => '#f5f4fb', 'surface' => '#ffffff', 'ink' => '#231c48', 'muted' => '#5c5680', 'primary' => '#3f3c89', 'primary2' => '#292760', 'accent' => '#9d8ed0', 'line' => '#ddd9ef'],
    'graphite' => ['bg' => '#f4f6f8', 'surface' => '#ffffff', 'ink' => '#17202b', 'muted' => '#546170', 'primary' => '#273444', 'primary2' => '#16202d', 'accent' => '#8b9db1', 'line' => '#d7dde5'],
    'sunset' => ['bg' => '#fcf3f2', 'surface' => '#ffffff', 'ink' => '#451b22', 'muted' => '#8b4a58', 'primary' => '#ab475b', 'primary2' => '#742d3d', 'accent' => '#d89ba7', 'line' => '#efd3d8'],
    'teal-gold' => ['bg' => '#eef7f5', 'surface' => '#ffffff', 'ink' => '#123632', 'muted' => '#456863', 'primary' => '#1e5f57', 'primary2' => '#123f3a', 'accent' => '#c6a65a', 'line' => '#d3e4df'],
    'midnight' => ['bg' => '#0d1320', 'surface' => '#131d2d', 'ink' => '#edf2f7', 'muted' => '#9eacbe', 'primary' => '#3c7adf', 'primary2' => '#244f9a', 'accent' => '#7bc4d6', 'line' => '#2f3d55'],
    'mint' => ['bg' => '#edf6f1', 'surface' => '#ffffff', 'ink' => '#14382e', 'muted' => '#4a766a', 'primary' => '#1f7964', 'primary2' => '#155445', 'accent' => '#7fb8a0', 'line' => '#d4e6db'],
    'sandstone' => ['bg' => '#fef7ed', 'surface' => '#fffaf3', 'ink' => '#7c2d12', 'muted' => '#9a3412', 'primary' => '#c2410c', 'primary2' => '#9a3412', 'accent' => '#f59e0b', 'line' => '#fed7aa'],
    'plum' => ['bg' => '#f8f4fa', 'surface' => '#ffffff', 'ink' => '#38213f', 'muted' => '#6a4f72', 'primary' => '#70467b', 'primary2' => '#4d2f55', 'accent' => '#b18cbb', 'line' => '#e4d7e7'],
];

$themeTweaks = [
    'theme2' => ['hero' => 'from-[var(--primary2)] via-[var(--primary)] to-slate-950', 'card' => 'shadow-2xl border border-[var(--line)]/80'],
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
        ['Checking', 'Checking.php'],
        ['Savings', 'Savings.php'],
        ['Money Market', 'Money-Market.php'],
        ['Certificates of Deposit', 'Certificates-of-Deposit.php'],
        ['Debit Cards', 'Debit-Cards.php'],
        ['Check Orders', 'Check-Orders.php'],
    ],
    'Business' => [
        ['Business Checking', 'Business-Checking.php'],
        ['Business Savings', 'Business-Savings.php'],
        ['Business Money Market', 'Business-Money-Market.php'],
        ['Business Certificates of Deposit', 'Business-Certificates-of-Deposit.php'],
        ['Business Debit Cards', 'Business-Debit-Cards.php'],
        ['Payments & Receivables', 'Payment-and-Receivables.php'],
    ],
    'Digital Banking' => [
        ['Online Banking', 'Online-Banking.php'],
        ['Business Online Banking', 'Business-Online-Banking.php'],
        ['Mobile Banking', 'Mobile-Banking.php'],
        ['Security Center', 'Security-Center.php'],
    ],
    'About Us' => [
        ['Company Profile', 'Company-Profile.php'],
        ['Locations', 'Locations.php'],
        ['Contact Us', 'Contact-Us.php'],
        ['News & Press Releases', 'News-and-Press-Releases.php'],
        ['Shareholder Information', 'Shareholder-Information.php'],
        ['Site Map', 'Site-Map.php'],
    ],
];

$scriptDir = rtrim(str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/themes/theme2/index.php'))), '/');
if ($scriptDir === '' || $scriptDir === '.') {
    $scriptDir = '';
}
$appBasePath = preg_replace('#/themes/[^/]+$#', '', $scriptDir);
if (!is_string($appBasePath) || $appBasePath === '.' || $appBasePath === '/') {
    $appBasePath = $appBasePath === '/' ? '' : (string)$appBasePath;
}
$themePageBase = ($appBasePath === '' ? '' : $appBasePath) . '/themes/theme2';
$themeAssetBase = ($appBasePath === '' ? '' : $appBasePath) . '/themes/theme1/';

function app_url(string $path = ''): string {
    $base = rtrim((string)($GLOBALS['appBasePath'] ?? ''), '/');
    $clean = ltrim($path, '/');
    if ($clean === '') {
        return $base === '' ? '/' : $base . '/';
    }
    return ($base === '' ? '' : $base) . '/' . $clean;
}

function theme_page_url(string $path = ''): string {
    $clean = ltrim($path, '/');
    if ($clean === '' || strcasecmp($clean, 'index.php') === 0) {
        return app_url('');
    }
    return app_url($clean);
}

function theme_asset_url(string $path): string {
    $base = $GLOBALS['themeAssetBase'] ?? '/themes/theme1/';
    return $base . ltrim($path, '/');
}

function nav_is_active(string $href, string $currentPage): bool {
    $target = basename((string)strtok($href, '#'));
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

function theme_find_group(array $navColumns, string $currentPage): ?string {
    foreach ($navColumns as $group => $links) {
        if (nav_group_active($links, $currentPage)) {
            return $group;
        }
    }
    return null;
}

function theme_section_links(array $navColumns, ?string $group): array {
    if ($group !== null && isset($navColumns[$group])) {
        return $navColumns[$group];
    }

    $merged = [];
    foreach (['Personal', 'Business', 'Digital Banking'] as $name) {
        if (isset($navColumns[$name])) {
            $merged = array_merge($merged, array_slice($navColumns[$name], 0, 2));
        }
    }
    return $merged;
}

function theme_page_profile(string $currentPage, array $navColumns): array {
    $homePages = ['index.php', 'index-2.php'];
    if (in_array($currentPage, $homePages, true)) {
        return [
            'group' => null,
            'eyebrow' => 'Private and Business Banking',
            'summary' => 'Professional banking services designed for everyday clients, commercial operators, and institutions that expect dependable access, responsive support, and secure digital delivery.',
            'actions' => [
                ['label' => 'Explore Personal Banking', 'href' => 'Checking.php', 'variant' => 'primary'],
                ['label' => 'Explore Business Banking', 'href' => 'Business-Checking.php', 'variant' => 'secondary'],
            ],
            'pillars' => [
                ['label' => 'Relationship Focused', 'text' => 'Clear support across branch, phone, and digital channels.'],
                ['label' => 'Secure Access', 'text' => 'Modern online banking backed by security-first controls.'],
                ['label' => 'Full Coverage', 'text' => 'Deposits, cards, treasury tools, and lending support.'],
            ],
            'featured_links' => theme_section_links($navColumns, null),
            'hero_image' => theme_asset_url('images/subpage-photo.jpg'),
        ];
    }

    $group = theme_find_group($navColumns, $currentPage) ?? 'About Us';
    $profiles = [
        'Personal' => [
            'eyebrow' => 'Personal Banking',
            'summary' => 'Everyday accounts, savings solutions, and card services presented with the clarity and trust expected from a modern financial institution.',
            'actions' => [
                ['label' => 'View Locations', 'href' => 'Locations.php', 'variant' => 'primary'],
                ['label' => 'Contact Us', 'href' => 'Contact-Us.php', 'variant' => 'secondary'],
            ],
            'pillars' => [
                ['label' => 'Everyday Access', 'text' => 'Checking, savings, and deposit products for daily banking needs.'],
                ['label' => 'Digital Convenience', 'text' => 'Secure mobile and online access built into the client journey.'],
                ['label' => 'Trusted Guidance', 'text' => 'Support from bankers who understand local and practical needs.'],
            ],
        ],
        'Business' => [
            'eyebrow' => 'Business Banking',
            'summary' => 'Professional banking tools for commercial operators who need dependable cash management, deposit products, and responsive relationship support.',
            'actions' => [
                ['label' => 'Contact a Banker', 'href' => 'Contact-Us.php', 'variant' => 'primary'],
                ['label' => 'See Treasury Services', 'href' => 'Payment-and-Receivables.php', 'variant' => 'secondary'],
            ],
            'pillars' => [
                ['label' => 'Operational Control', 'text' => 'Accounts and payment tools that support day-to-day business activity.'],
                ['label' => 'Responsive Service', 'text' => 'Banking support aligned with active commercial workflows.'],
                ['label' => 'Growth Ready', 'text' => 'Products structured for stability, reserves, and expansion.'],
            ],
        ],
        'Digital Banking' => [
            'eyebrow' => 'Digital Banking',
            'summary' => 'Secure online and mobile experiences designed for clients who expect uninterrupted access, practical controls, and simple account visibility.',
            'actions' => [
                ['label' => 'Review Security', 'href' => 'Security-Center.php', 'variant' => 'primary'],
                ['label' => 'Find Support', 'href' => 'Contact-Us.php', 'variant' => 'secondary'],
            ],
            'pillars' => [
                ['label' => 'Protected Sessions', 'text' => 'Security-focused access paths for personal and business users.'],
                ['label' => 'Always Available', 'text' => 'Online and mobile delivery designed for continuous access.'],
                ['label' => 'Payment Ready', 'text' => 'Digital tools for transfers, receivables, and account management.'],
            ],
        ],
        'About Us' => [
            'eyebrow' => 'Institutional Information',
            'summary' => 'Corporate information, branch access, and service resources presented with a more formal and internationally familiar banking layout.',
            'actions' => [
                ['label' => 'Find a Branch', 'href' => 'Locations.php', 'variant' => 'primary'],
                ['label' => 'Contact Us', 'href' => 'Contact-Us.php', 'variant' => 'secondary'],
            ],
            'pillars' => [
                ['label' => 'Client Support', 'text' => 'Clear access to contacts, branch details, and service resources.'],
                ['label' => 'Corporate Clarity', 'text' => 'Information presented in a familiar institutional structure.'],
                ['label' => 'Service Trust', 'text' => 'Security, disclosures, and company details kept close at hand.'],
            ],
        ],
    ];

    $profile = $profiles[$group] ?? $profiles['About Us'];
    $profile['group'] = $group;
    $profile['featured_links'] = array_slice(theme_section_links($navColumns, $group), 0, 6);
    $profile['hero_image'] = theme_asset_url('images/subpage-photo.jpg');
    return $profile;
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

    $middle = preg_replace_callback('/<img\b[^>]*>/i', static function ($match) {
        $tag = $match[0];
        $tag = preg_replace('/\s(?:width|height)\s*=\s*("|\').*?\1/i', '', $tag);
        $tag = preg_replace_callback('/\sstyle\s*=\s*("|\')(.*?)\1/i', static function ($m) {
            $styles = preg_replace('/(^|;)\s*(?:width|max-width|height)\s*:[^;]*/i', '$1', $m[2]);
            $styles = trim((string)preg_replace('/;+/', ';', (string)$styles), ' ;');
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
            $quote = $mm[2];
            $url = trim($mm[3]);

            if ($url === '' ||
                preg_match('/^(?:https?:|data:|mailto:|tel:|javascript:|#|\/)/i', $url) ||
                str_starts_with($url, '/themes/') ||
                str_starts_with($url, 'asset.php') ||
                str_starts_with($url, '/asset.php')) {
                return $mm[0];
            }

            if ($attr === 'href' && preg_match('/\.(?:php|html?)(?:$|[?#])/i', $url)) {
                return $mm[0];
            }

            if (!preg_match('/\.(?:png|jpe?g|gif|svg|webp|ico|css|js|woff2?|ttf|otf|eot|map)(?:$|[?#])/i', $url)) {
                return $mm[0];
            }

            return $attr . '=' . $quote . theme_asset_url($url) . $quote;
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

$resolvedCurrentPage = $sourcePage ?? ($_GET['_page'] ?? basename((string)($_SERVER['SCRIPT_NAME'] ?? 'index.php')));
$currentPage = basename((string)$resolvedCurrentPage);
$currentPage = preg_replace('/[^A-Za-z0-9._-]/', '', $currentPage);
if ($currentPage === '') {
    $currentPage = 'index.php';
}
$isHomePage = in_array($currentPage, ['index.php', 'index-2.php'], true);

$pageTitle = preg_replace('/\.php$/', '', str_replace(['-', '_'], ' ', $currentPage));
if (strcasecmp($pageTitle, 'index') === 0) {
    $pageTitle = 'Home';
}

$siteYear = trim((string)($site['year'] ?? ''));
if ($siteYear === '' || strcasecmp($siteYear, 'Copyright') === 0) {
    $site['year'] = 'Copyright ' . date('Y') . ' ' . ($site['name'] ?? 'Community Trust Bank') . '. All rights reserved.';
}

$loginSection = trim((string)($site['login'] ?? 'user'), '/');
if ($loginSection === '') {
    $loginSection = 'user';
}

$utilityLinks = [
    ['Contact Us', 'Contact-Us.php'],
    ['Locations', 'Locations.php'],
    ['Security Center', 'Security-Center.php'],
    ['Site Map', 'Site-Map.php'],
];

$clientActions = [
    ['label' => 'Login', 'href' => app_url($loginSection . '/login.php'), 'variant' => 'primary'],
    ['label' => 'Register', 'href' => app_url($loginSection . '/register.php'), 'variant' => 'secondary'],
];

$homeSlides = [
    [
        'eyebrow' => 'Business Banking',
        'text' => 'Commercial banking, treasury support, and responsive service for businesses that need dependable execution.',
        'image' => 'img/ContentImageHandler99159915.jpg?ImageId=83765',
        'primary' => ['label' => 'Meet a Banker', 'href' => 'Find-My-Banker.php'],
        'secondary' => ['label' => 'Business Banking', 'href' => 'Business-Checking.php'],
    ],
    [
        'eyebrow' => 'Personal Banking',
        'text' => 'Checking, savings, and deposit products delivered through a calmer, more professional digital experience.',
        'image' => 'img/slide.jpg?ImageId=110220',
        'primary' => ['label' => 'Explore Accounts', 'href' => 'Checking.php'],
        'secondary' => ['label' => 'View Locations', 'href' => 'Locations.php'],
    ],
    [
        'eyebrow' => 'Digital Access',
        'text' => 'Online and mobile banking designed for secure access, account visibility, and day-to-day convenience.',
        'image' => 'img/ContentImageHandler1b331b33.jpg?ImageId=110220',
        'primary' => ['label' => 'Online Banking', 'href' => 'Online-Banking.php'],
        'secondary' => ['label' => 'Security Center', 'href' => 'Security-Center.php'],
    ],
];

$homePrimaryCards = [
    [
        'title' => 'Personal Banking',
        'text' => 'Everyday accounts and savings solutions for clients who want clarity, access, and reliable service.',
        'href' => 'Checking.php',
        'label' => 'Explore Personal Banking',
        'image' => 'images/subpage-photo.jpg',
    ],
    [
        'title' => 'Business Banking',
        'text' => 'Commercial products and relationship-led support for operations, reserves, and growth planning.',
        'href' => 'Business-Checking.php',
        'label' => 'Explore Business Banking',
        'image' => 'img/ContentImageHandler99159915.jpg?ImageId=83765',
    ],
    [
        'title' => 'Digital Banking',
        'text' => 'Secure online and mobile access for transfers, account visibility, and daily banking control.',
        'href' => 'Online-Banking.php',
        'label' => 'Explore Digital Banking',
        'image' => 'img/slide.jpg?ImageId=110220',
    ],
];

$homeFeatureCards = [
    [
        'title' => 'Control Your Debit Card',
        'text' => 'Manage card usage and spending with tools built for faster oversight and client confidence.',
        'href' => 'Debit-Cards.php#cardvalet',
        'label' => 'Learn More',
        'art' => 'card',
        'badge' => 'CARD',
    ],
    [
        'title' => 'Allpoint ATM Network',
        'text' => 'Access cash through a broad surcharge-free ATM network at well-known retail locations.',
        'href' => 'speedbumpee02ee02.php?link=https://www.allpointnetwork.com/',
        'label' => 'Locate Access',
        'art' => 'atm',
        'badge' => 'ATM',
    ],
    [
        'title' => 'Remote Deposit Capture',
        'text' => 'Digitize business deposits with tools that reduce trips and improve processing convenience.',
        'href' => 'Payment-and-Receivables.php#Remote-Deposit-Capture',
        'label' => 'View Service',
        'art' => 'rdc',
        'badge' => 'RDC',
    ],
];

$homeInsightCards = [
    [
        'title' => 'Financial Results',
        'text' => 'Review current performance updates and institutional disclosures.',
        'href' => 'Financial-Results.php',
        'image' => 'img/ContentImageHandler4b274b27.jpg?imageId=83783',
    ],
    [
        'title' => 'News Room',
        'text' => 'See announcements, press releases, and recent updates from the bank.',
        'href' => 'News-and-Press-Releases.php',
        'image' => 'img/ContentImageHandler4a724a72.jpg',
    ],
];

$homeQuickActions = [
    [
        'title' => 'Open a Personal Account',
        'text' => 'Compare everyday checking and savings options for your banking profile.',
        'href' => 'Checking.php',
    ],
    [
        'title' => 'Business Services',
        'text' => 'Access account, treasury, and payment services for commercial operations.',
        'href' => 'Business-Checking.php',
    ],
    [
        'title' => 'Online Banking Access',
        'text' => 'Review digital tools for payments, monitoring, and secure account access.',
        'href' => 'Online-Banking.php',
    ],
    [
        'title' => 'Find a Location',
        'text' => 'Locate nearby branches and ATM access points for in-person support.',
        'href' => 'Locations.php',
    ],
    [
        'title' => 'Security & Fraud Help',
        'text' => 'Read security guidance and immediate steps for suspicious activity.',
        'href' => 'Security-Center.php',
    ],
    [
        'title' => 'Contact a Banker',
        'text' => 'Speak with support for account, product, or service-related questions.',
        'href' => 'Contact-Us.php',
    ],
];

$homeTrustSignals = [
    [
        'label' => 'Security Controls',
        'text' => 'Multi-layered authentication and secure session handling for digital channels.',
    ],
    [
        'label' => 'Account Visibility',
        'text' => 'Clear personal and business account access through online and mobile services.',
    ],
    [
        'label' => 'Client Support',
        'text' => 'Direct support pathways for branch, phone, and service desk engagement.',
    ],
    [
        'label' => 'Regulatory Alignment',
        'text' => 'Disclosures, privacy, and security resources maintained for client reference.',
    ],
];

$homeServiceFacts = [
    ['label' => 'Digital Banking Access', 'value' => '24/7'],
    ['label' => 'Client Support Channels', 'value' => 'Phone, Email, Branch'],
    ['label' => 'Core Segments', 'value' => 'Personal & Business'],
    ['label' => 'Payment Services', 'value' => 'Transfers, ACH, Receivables'],
];

$pageProfile = theme_page_profile($currentPage, $navColumns);
$sectionLinks = $pageProfile['featured_links'] ?? theme_section_links($navColumns, $pageProfile['group'] ?? null);
