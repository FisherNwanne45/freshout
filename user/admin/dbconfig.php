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
		$configFile = dirname(__DIR__, 2) . '/config.php';
		if (!file_exists($configFile)) {
			throw new Exception("Configuration file not found: $configFile");
		}

		// Include the config file and capture its variables
		$defaultConfig = null;
		$APP_CONFIG = null;
		require $configFile;

		// Determine the effective db config
		if (isset($APP_CONFIG) && is_array($APP_CONFIG) && isset($APP_CONFIG['db'])) {
			$db = $APP_CONFIG['db'];
		} elseif (isset($defaultConfig) && is_array($defaultConfig) && isset($defaultConfig['db'])) {
			$db = $defaultConfig['db'];
		} else {
			throw new Exception('No database configuration found in config.php');
		}

		// Validate required keys (no fallbacks)
		if (empty($db['host'])) throw new Exception('Database host not configured');
		if (empty($db['name'])) throw new Exception('Database name not configured');
		if (empty($db['username'])) throw new Exception('Database username not configured');
		if (!array_key_exists('password', $db)) throw new Exception('Database password not configured');

		$this->host = (string)$db['host'];
		$this->db_name = (string)$db['name'];
		$this->username = (string)$db['username'];
		$this->password = (string)$db['password'];
	}

	public function dbConnection()
	{
		$this->conn = null;
		$options = [
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES => false,
		];

		try {
			$dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
			$this->conn = new PDO($dsn, $this->username, $this->password, $options);
		} catch (PDOException $exception) {
			// Optional socket fallback (still uses same credentials)
			if (strpos($exception->getMessage(), '[2002] No such file or directory') !== false) {
				try {
					$fallbackDsn = "mysql:host=127.0.0.1;dbname={$this->db_name};charset=utf8mb4";
					$this->conn = new PDO($fallbackDsn, $this->username, $this->password, $options);
				} catch (PDOException $fallbackException) {
					throw new Exception("Connection error: " . $fallbackException->getMessage());
				}
			} else {
				throw new Exception("Connection error: " . $exception->getMessage());
			}
		}

		return $this->conn;
	}
}
