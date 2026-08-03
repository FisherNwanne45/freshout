<?php
/**
 * Theme1b config stub.
 * Delegates to root config (provides $conn, $APP_CONFIG, etc.),
 * then populates the legacy flat variables that theme1b templates use.
 */
require_once dirname(__DIR__, 2) . '/config.php';

/** @var mysqli $conn */

// Shared admin-driven favicon URL (absolute).
include_once dirname(__DIR__, 2) . '/private/shared-favicon-url.php';
$favicon_url = $sharedFaviconUrl ?? '';

// Resolve app base path (e.g. /fresh) from current request.
$appBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
if ($appBase === '') {
	$appBase = '';
}

// ── Bind legacy variables from DB (site first, then legacy settings) ──
$_site = $conn->query("SELECT * FROM site LIMIT 1");
$_siteRow = $_site ? ($_site->fetch_assoc() ?: []) : [];

if (empty($_siteRow)) {
	$_settings = $conn->query("SELECT * FROM settings LIMIT 1");
	$_settingsRow = $_settings ? ($_settings->fetch_assoc() ?: []) : [];
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
			'translate' => $_settingsRow['translate'] ?? '',
			'register' => $_settingsRow['register'] ?? '/user/register.php',
		];
	}
}

$name        = $_siteRow['name']  ?? '';
$email       = $_siteRow['email'] ?? '';
$addr        = $_siteRow['addr']  ?? '';
$phone       = $_siteRow['phone'] ?? '';
$url         = $_siteRow['url']   ?? '';
$image       = basename((string)($_siteRow['image'] ?? ''));
$livechat    = $_siteRow['tawk']  ?? '';
$login       = '/' . ($_siteRow['login'] ?? 'user');
$country     = $_siteRow['country'] ?? '';
$curr        = $_siteRow['currency'] ?? '';
$translate   = '';
$register    = $_siteRow['register'] ?? '/user/register.php';
$officer     = '';
$officer2    = '';
$officer3    = '';
$footertext  = 'Equal Housing Lender | Equal Opportunity Employer | Member FDIC';
$slidertext  = 'Your Gateway to Financial Privacy and Security';
$sliderBOLD  = 'Secure Future, Beyond Borders';

// Logo URL — use current admin-uploaded logo when available.
$logoAbs = dirname(__DIR__, 2) . '/user/admin/site/' . $image;
if ($image !== '' && is_file($logoAbs)) {
	$logo_url = $appBase . '/user/admin/site/' . rawurlencode($image);
} else {
	// Fallback to bundled theme logo if admin logo is missing.
	$logo_url = 'images/logo.png';
}

// OG image should follow the admin branding assets.
$og_image_url = $favicon_url !== '' ? $favicon_url : $logo_url;

// Reuse the same translator widget used in login/auth screens.
ob_start();
include dirname(__DIR__, 2) . '/private/shared-translator.php';
$translate = ob_get_clean();

unset($_site, $_siteRow, $_settings, $_settingsRow, $logoAbs, $appBase, $sharedFaviconUrl);
