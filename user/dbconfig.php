<?php
class Database
{
	private $host;
	private $db_name;
	private $username;
	private $password;
	public $conn;

	public function __construct()
	{
		// Absolute path from this file's location: /an/user/ -> /an/config.php
		$configFile = dirname(__DIR__) . '/config.php';
		if (!file_exists($configFile)) {
			throw new Exception("Configuration file not found: $configFile");
		}

		// Include and capture variables – no reliance on globals
		$defaultConfig = null;
		$APP_CONFIG = null;
		require $configFile;

		// Default fallback (safe for local dev)
		$db = [
			'host' => '127.0.0.1',
			'name' => 'fresh',
			'username' => 'root',
			'password' => '',
		];

		// Override with real configuration if present
		if (isset($APP_CONFIG) && is_array($APP_CONFIG) && isset($APP_CONFIG['db']) && is_array($APP_CONFIG['db'])) {
			$db = array_replace($db, $APP_CONFIG['db']);
		} elseif (isset($defaultConfig) && is_array($defaultConfig) && isset($defaultConfig['db'])) {
			$db = array_replace($db, $defaultConfig['db']);
		}

		$this->host = (string)$db['host'];
		$this->db_name = (string)$db['name'];
		$this->username = (string)$db['username'];
		$this->password = (string)$db['password'];
	}

	public function dbConnection()
	{
		$this->conn = null;
		$hostsToTry = array_values(array_unique([$this->host, '127.0.0.1']));
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];
		$lastError = '';

		foreach ($hostsToTry as $host) {
			try {
				$dsn = "mysql:host={$host};dbname={$this->db_name};charset=utf8mb4";
				$this->conn = new PDO($dsn, $this->username, $this->password, $options);
				break;
			} catch (PDOException $exception) {
				$lastError = $exception->getMessage();
				$this->conn = null;
			}
		}

		if ($this->conn === null) {
			echo "Connection error: " . $lastError;
		}

		return $this->conn;
	}
}
