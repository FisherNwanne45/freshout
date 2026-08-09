<?php
require_once dirname(__DIR__, 2) . '/config.php';

/** @var mysqli|null $conn */
if (!isset($conn) || !($conn instanceof mysqli)) {
    global $conn;
}

include_once dirname(__DIR__, 2) . '/private/shared-favicon-url.php';

// Resolve public app base path for both subfolder installs (/freshout)
// and vhost installs where this project is the web root (/).
$projectRootPath = realpath(dirname(__DIR__, 2)) ?: dirname(__DIR__, 2);
$documentRootPath = isset($_SERVER['DOCUMENT_ROOT']) ? (realpath((string) $_SERVER['DOCUMENT_ROOT']) ?: (string) $_SERVER['DOCUMENT_ROOT']) : '';
$appBase = '';

if ($documentRootPath !== '' && str_starts_with($projectRootPath, $documentRootPath)) {
    $relative = trim(str_replace('\\', '/', substr($projectRootPath, strlen($documentRootPath))), '/');
    $appBase = $relative === '' ? '' : '/' . $relative;
} else {
    // Fallback for unusual server mappings where DOCUMENT_ROOT is unreliable.
    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $projectRootFolderName = basename($projectRootPath);
    if ($projectRootFolderName !== '' && strpos($scriptName, '/' . $projectRootFolderName) !== false) {
        $endPos = strpos($scriptName, '/' . $projectRootFolderName) + strlen('/' . $projectRootFolderName);
        $appBase = rtrim(substr($scriptName, 0, $endPos), '/');
    }
}

if ($appBase === '/' || $appBase === '.' || $appBase === '') {
    $appBase = '';
}

if (!defined('ACTIVE_THEME')) {
    $activeTheme = '';
    try {
        if (isset($conn) && $conn instanceof mysqli) {
            $themeResult = $conn->query("SELECT `value` FROM site_settings WHERE `key`='theme' LIMIT 1");
            if ($themeResult && $themeResult->num_rows > 0) {
                $candidateTheme = (string) ($themeResult->fetch_assoc()['value'] ?? '');
                if (preg_match('/^[a-zA-Z0-9_-]+$/', $candidateTheme)) {
                    $activeTheme = $candidateTheme;
                }
            }
        }
    } catch (Throwable $e) {
        $activeTheme = '';
    }

    if ($activeTheme === '') {
        $activeTheme = basename(__DIR__);
    }

    define('ACTIVE_THEME', $activeTheme);
}

$_siteRow = [];
try {
    if (isset($conn) && $conn instanceof mysqli) {
        $_siteQuery = $conn->query("SELECT * FROM site ORDER BY id ASC LIMIT 1");
        if ($_siteQuery && $_siteQuery->num_rows > 0) {
            $_siteRow = $_siteQuery->fetch_assoc() ?: [];
        }
    }
} catch (Throwable $e) {
    $_siteRow = [];
}

if (empty($_siteRow)) {
    try {
        if (isset($conn) && $conn instanceof mysqli) {
            $_settingsQuery = $conn->query("SELECT * FROM settings ORDER BY id ASC LIMIT 1");
            $_settingsRow = $_settingsQuery ? ($_settingsQuery->fetch_assoc() ?: []) : [];
            if (!empty($_settingsRow)) {
                $_siteRow = [
                    'name' => $_settingsRow['url_name'] ?? '',
                    'email' => $_settingsRow['url_email'] ?? '',
                    'addr' => $_settingsRow['url_address'] ?? '',
                    'phone' => $_settingsRow['url_tel'] ?? '',
                    'url' => $_settingsRow['url_link'] ?? '',
                    'image' => $_settingsRow['image'] ?? '',
                    'tawk' => $_settingsRow['tawk'] ?? '',
                    'login' => $_settingsRow['login'] ?? 'user',
                    'country' => $_settingsRow['country'] ?? '',
                    'currency' => $_settingsRow['currency'] ?? '',
                    'register' => $_settingsRow['register'] ?? '/user/register.php',
                ];
            }
        }
    } catch (Throwable $e) {
        $_siteRow = [];
    }
}

$name = trim((string)($_siteRow['name'] ?? 'FreshOut'));
$email = trim((string)($_siteRow['email'] ?? ''));
$email2 = '';
if (isset($_siteRow['email2']) && trim((string)$_siteRow['email2']) !== '') {
    $email2 = trim((string)$_siteRow['email2']);
} elseif (isset($_settingsRow) && is_array($_settingsRow)) {
    $email2 = trim((string)($_settingsRow['url_email2'] ?? $_settingsRow['email2'] ?? ''));
}
$addr = trim((string)($_siteRow['addr'] ?? ''));
$phone = trim((string)($_siteRow['phone'] ?? ''));
$country = trim((string)($_siteRow['country'] ?? ''));
$curr = trim((string)($_siteRow['currency'] ?? ''));
$livechat = trim((string)($_siteRow['tawk'] ?? ''));
$image = basename((string)($_siteRow['image'] ?? ''));

$siteUrl = $appBase;

$url = $siteUrl;

$normalizeThemePath = static function ($value) use ($appBase): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (strpos($value, 'http://') === 0 || strpos($value, 'https://') === 0) {
        return $value;
    }

    if (strpos($value, '/') === 0) {
        return ($appBase === '' ? '' : $appBase) . $value;
    }

    return ($appBase === '' ? '' : $appBase) . '/' . ltrim($value, '/');
};

$loginTarget = trim((string)($_siteRow['login'] ?? 'user'));
if ($loginTarget === '') {
    $loginTarget = 'user';
}
$login = $normalizeThemePath($loginTarget);

$registerTarget = trim((string)($_siteRow['register'] ?? '/user/register.php'));
$register = $normalizeThemePath($registerTarget);

if ($login === '') {
    $login = ($appBase === '' ? '' : $appBase) . '/user/login.php';
}
if ($register === '') {
    $register = ($appBase === '' ? '' : $appBase) . '/user/register.php';
}

$translate = '';
ob_start();
include dirname(__DIR__, 2) . '/private/shared-translator.php';
$translate = ob_get_clean();

$footertext = 'Equal Housing Lender | Equal Opportunity Employer';
$slidertext = 'Your Gateway to Financial Privacy and Security';
$sliderBOLD = 'Secure Future, Beyond Borders';

$favicon_url = $sharedFaviconUrl ?? '';
$logo_url = '';
$logoAbs = dirname(__DIR__, 2) . '/user/admin/site/' . $image;
if ($image !== '' && is_file($logoAbs)) {
    $logo_url = ($appBase === '' ? '' : $appBase) . '/user/admin/site/' . rawurlencode($image);
} elseif ($image !== '') {
    $logo_url = ($appBase === '' ? '' : $appBase) . '/admin/assets/images/logo/' . rawurlencode($image);
}

unset($_siteQuery, $_settingsQuery, $_settingsRow, $_siteRow, $logoAbs, $sharedFaviconUrl);
