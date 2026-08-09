<?php
session_start();
require_once __DIR__ . '/session.php';
require_once dirname(__DIR__, 2) . '/config.php';

if (!isset($_SESSION['email'])) {
	header('Location: login.php');
	exit();
}

$return = (string)($_GET['return'] ?? 'index.php');
if ($return === '' || strpos($return, '..') !== false || strpos($return, '://') !== false) {
	$return = 'index.php';
}

$ok = true;
$messages = [];

if (function_exists('opcache_reset')) {
	try {
		if (!opcache_reset()) {
			$ok = false;
			$messages[] = 'OPcache reset returned false.';
		}
	} catch (Throwable $e) {
		$ok = false;
		$messages[] = $e->getMessage();
	}
}

if (function_exists('apcu_clear_cache')) {
	try {
		if (!apcu_clear_cache()) {
			$messages[] = 'APCu clear returned false.';
		}
	} catch (Throwable $e) {
		$messages[] = $e->getMessage();
	}
}

clearstatcache(true);

$separator = (strpos($return, '?') !== false) ? '&' : '?';
if ($ok) {
	header('Location: ' . $return . $separator . 'cache_cleared=1');
	exit();
}

header('Location: ' . $return . $separator . 'cache_error=' . rawurlencode(implode(' ', $messages)));
exit();