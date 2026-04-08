<?php

$defaultConfig = [
	'db' => [
		'host' => 'localhost',
		'username' => 'root',
		'password' => '',
		'name' => 'fresh2'
	],
	'smtp' => [
		'host' => 'smtp.gmail.com',
		'port' => 465,
		'secure' => 'ssl',
		'username' => 'barfumehouse@gmail.com',
		'password' => 'khtx evms npbw nbyf',
		'from' => 'barfumehouse@gmail.com',
		'from_name' => 'Fisher Wallet',
		'reply_to' => 'barfumehouse@gmail.com'
	],
	'sms' => [
		'enabled' => '0',
		'provider' => 'textbelt',
		'brand_name' => 'Fisher Wallet',
		'twilio_sid' => '',
		'twilio_token' => '',
		'twilio_from' => '',
		'termii_api_key' => '',
		'termii_sender' => 'N-Alert',
		'textbelt_key' => 'textbelt'
	]
];

if (!isset($APP_CONFIG) || !is_array($APP_CONFIG)) {
	$APP_CONFIG = $defaultConfig;
} else {
	$APP_CONFIG = array_replace_recursive($defaultConfig, $APP_CONFIG);
}

if (!isset($APP_CONFIG['db']) || !is_array($APP_CONFIG['db'])) {
	$APP_CONFIG['db'] = $defaultConfig['db'];
}
if (!isset($APP_CONFIG['db']['host']) || trim((string)$APP_CONFIG['db']['host']) === '') {
	$APP_CONFIG['db']['host'] = $defaultConfig['db']['host'];
}
if (!isset($APP_CONFIG['db']['username']) || trim((string)$APP_CONFIG['db']['username']) === '') {
	$APP_CONFIG['db']['username'] = $defaultConfig['db']['username'];
}
if (!array_key_exists('password', $APP_CONFIG['db'])) {
	$APP_CONFIG['db']['password'] = $defaultConfig['db']['password'];
}
if (!isset($APP_CONFIG['db']['name']) || trim((string)$APP_CONFIG['db']['name']) === '') {
	$APP_CONFIG['db']['name'] = $defaultConfig['db']['name'];
}

// Backward compatibility for existing scripts.
$servername = $APP_CONFIG['db']['host'];
$username = $APP_CONFIG['db']['username'];
$password = $APP_CONFIG['db']['password'];
$dbname = $APP_CONFIG['db']['name'];

$smtphost = $APP_CONFIG['smtp']['host'];
$smtpport = $APP_CONFIG['smtp']['port'];
$smtpsecure = $APP_CONFIG['smtp']['secure'];
$user = $APP_CONFIG['smtp']['username'];
$pass = $APP_CONFIG['smtp']['password'];
$from = $APP_CONFIG['smtp']['from'];
$frname = $APP_CONFIG['smtp']['from_name'];
$reply = $APP_CONFIG['smtp']['reply_to'];

// Ensure $conn always targets the global scope so it is accessible outside
// of any method/constructor that triggers this file via require_once.
global $conn;
if (!isset($conn) || !($conn instanceof mysqli)) {
	$connectError = '';
	$hostsToTry = array_values(array_unique([$servername, '127.0.0.1']));

	foreach ($hostsToTry as $host) {
		try {
			$conn = new mysqli($host, $username, $password, $dbname);
			if (!$conn->connect_error) {
				$servername = $host;
				$APP_CONFIG['db']['host'] = $host;
				break;
			}
			$connectError = $conn->connect_error;
		} catch (mysqli_sql_exception $e) {
			$connectError = $e->getMessage();
			$conn = null;
		}
	}

	if (!($conn instanceof mysqli) || $conn->connect_error)
	{
		die("Connection failed: " . $connectError);
	}
}