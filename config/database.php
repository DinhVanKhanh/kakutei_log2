<?php
//require_once __DIR__ . '/../../../../common_files/connect_db.php';
require_once __DIR__ . ("/lib.php");
global $DB_SERVER_SERVICE_NAME, $NAME_SERVICE_DB, $USER_SERVICE_DB, $PASS_SERVICE_DB;
//   $host   = $DB_SERVER_SERVICE_NAME;
//   $user   = $USER_SERVICE_DB;
//   $pass   = $PASS_SERVICE_DB;
//   $dbname = $NAME_SERVICE_DB;

$host      = "localhost";
$user      = "root";
$pass      = "";
$dbname    = "service_db";

class Database
{
	public $conn = null, $stmt = null;
	protected $table = "";
	private $_host = "", $_user = "", $_pass = "", $_dbname = "";

	public function __construct()
	{
		global $host, $user, $pass, $dbname;
		$this->_host    = $host;
		$this->_user    = $user;
		$this->_pass    = $pass;
		$this->_dbname  = $dbname;
		$this->connect();
	}

	private function connect()
	{
		try {
			$this->conn = new PDO("mysql:host={$this->_host};dbname={$this->_dbname}", $this->_user, $this->_pass);
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->conn->exec('SET NAMES "utf8"');
		} catch (PDOException $e) {
			echo $e->getMessage();
			exit;
		}
	}

	public function __destruct()
	{
		$conn = $stmt = null;
		$table = "";
	}
}
