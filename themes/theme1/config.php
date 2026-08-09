<?php

/**
 * Theme stub — delegates to the root config.php.
 * Keeps existing include('config.php') calls in theme pages working.
 */
require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__, 2) . '/user/auth-theme.php';

if (!function_exists('theme1_dedupe_script_tags')) {
	function theme1_dedupe_script_tags(string $html): string
	{
		$seen = [];
		return (string)preg_replace_callback(
			'~<script\b[^>]*\bsrc=("|\")([^"\']+)(?:\?[^"\']*)?\1[^>]*>\s*</script>~i',
			static function (array $matches) use (&$seen): string {
				$src = strtolower(trim((string)($matches[2] ?? '')));
				$targets = [
					'js/vendor/jquery.mobile.custom.min.js',
					'js/jquery-scripts.js',
				];

				foreach ($targets as $target) {
					if ($src === $target || str_ends_with($src, '/' . $target)) {
						if (isset($seen[$target])) {
							return '';
						}
						$seen[$target] = true;
						break;
					}
				}

				return $matches[0];
			},
			$html
		);
	}
}

if (empty($GLOBALS['theme1ScriptDeduperStarted'])) {
	$GLOBALS['theme1ScriptDeduperStarted'] = true;
	ob_start('theme1_dedupe_script_tags');
}

/** @var mysqli|null $conn */
$conn = $conn ?? null;

$theme1ScriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
$theme1BasePath = '';
if ($theme1ScriptName !== '') {
	if (strpos($theme1ScriptName, '/themes/') !== false) {
		$theme1BasePath = substr($theme1ScriptName, 0, strpos($theme1ScriptName, '/themes/'));
	} else {
		$theme1BasePath = rtrim(dirname($theme1ScriptName), '/');
	}
}

$theme1NormalizeAssetUrl = static function (string $url): string {
	global $theme1BasePath;
	$url = trim($url);
	if ($url === '' || preg_match('~^(?:[a-z]+:)?//~i', $url) || str_starts_with($url, '/')) {
		return $url;
	}

	$base = $theme1BasePath !== '' ? $theme1BasePath : '';

	return $base . '/user/' . ltrim($url, '/');
};

$theme1SiteLogoUrl = '';
if ($conn instanceof mysqli) {
	try {
		$theme1Site = $conn->query('SELECT image FROM site LIMIT 1');
		$theme1SiteRow = $theme1Site ? ($theme1Site->fetch_assoc() ?: []) : [];
		$theme1LogoFile = basename((string)($theme1SiteRow['image'] ?? ''));
		if ($theme1LogoFile !== '') {
			$theme1LogoAbs = dirname(__DIR__, 2) . '/user/admin/site/' . $theme1LogoFile;
			if (is_file($theme1LogoAbs)) {
				$theme1SiteLogoUrl = $theme1NormalizeAssetUrl('admin/site/' . rawurlencode($theme1LogoFile));
			}
		}
	} catch (Throwable $e) {
	}
}

$theme1AuthLogoUrl = '';
$theme1DashboardLogoUrl = '';
if ($conn instanceof mysqli) {
	try {
		$theme1AuthLogoUrl = $theme1NormalizeAssetUrl(html_entity_decode((string)get_auth_logo_url($conn), ENT_QUOTES, 'UTF-8'));
		$theme1DashboardLogoUrl = $theme1NormalizeAssetUrl(html_entity_decode((string)get_dashboard_logo_url($conn), ENT_QUOTES, 'UTF-8'));
	} catch (Throwable $e) {
	}
}

if ($theme1AuthLogoUrl === '') {
	$theme1AuthLogoUrl = $theme1SiteLogoUrl;
}

if ($theme1DashboardLogoUrl === '') {
	$theme1DashboardLogoUrl = $theme1SiteLogoUrl;
}
