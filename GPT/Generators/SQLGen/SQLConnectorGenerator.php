<?php

class SQLConnectorGenerator {
	private $dbName;
	private $username;
	private $password;

	public function __construct($dbName, $username, $password) {
		$this->dbName = $dbName;
		$this->username = $username;
		$this->password = $password;
	}

	public function generate() {
		$content = "<?php

class SQLConnector {
	private static \$instance = null;
	private \$pdo;

	private function __construct() {
		\$dbName = '{$this->dbName}';
		\$username = '{$this->username}';
		\$password = '{$this->password}';
		\$host = 'localhost';

		try {
			\$this->pdo = new PDO(\"mysql:host=\$host;dbname=\$dbName\", \$username, \$password);
			\$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException \$e) {
			die(\"Connection failed: \" . \$e->getMessage());
		}
	}

	public static function getInstance() {
		if (self::\$instance === null) {
			self::\$instance = new self();
		}
		return self::\$instance;
	}

	public function getConnection() {
		return \$this->pdo;
	}
}
";

		$dir = basename(__DIR__)."/AI/".$this->dbName."/".$tableName;
		if(!is_dir($dir)) mkdir($dir, 0777, true);
		file_put_contents("{$dir}/SQLConnector.php", $content);
		echo "Generated {$dir}/SQLConnector.php\n";
	}
}