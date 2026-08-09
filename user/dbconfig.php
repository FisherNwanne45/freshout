<?php
class Database
{
     
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct()
	{
		require_once dirname(__DIR__) . '/config.php';

		$cfg = [];
		if (isset($APP_CONFIG) && is_array($APP_CONFIG)) {
			$cfg = $APP_CONFIG;
		} elseif (isset($GLOBALS['APP_CONFIG']) && is_array($GLOBALS['APP_CONFIG'])) {
			$cfg = $GLOBALS['APP_CONFIG'];
		}

		if (!isset($cfg['db']) || !is_array($cfg['db'])) {
			throw new RuntimeException('Database configuration is missing in config.php');
		}

		$db = $cfg['db'];
		$this->host = (string)($db['host'] ?? '');
		$this->db_name = (string)($db['name'] ?? '');
		$this->username = (string)($db['username'] ?? '');
		$this->password = (string)($db['password'] ?? '');
		$this->port = (int)($db['port'] ?? 3306) ?: 3306;

		if ($this->host === '' || $this->db_name === '' || $this->username === '') {
			throw new RuntimeException('Incomplete database configuration in config.php');
		}
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
			$dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
			$this->conn = new PDO($dsn, $this->username, $this->password, $options);
		} catch (PDOException $exception) {
			error_log('db connection error: ' . $exception->getMessage());
		}


		return $this->conn;
    }
}
?>