#!/usr/bin/env php
<?php
/**
 * Generates mysql PDO classes from all the database tables within the database. Basic CRUD handling, select, insert, update and delete.
 *
 * Open your terminal to this directory and run:
 * > php generate.php <database_name> <username> <password>
 * replace the <value> with your mysql credentials
 * example:
 * php generate.php my_special_table root password!
 */

if (count($argv) < 3) {
	echo "Usage: php generate.php <database_name> <username>\n";
	exit(1);
}

$dbName = $argv[1];
$username = $argv[2];
$password = $argv[3] ?? '';

try {
	$pdo = new PDO("mysql:host=127.0.0.1;dbname=$dbName", $username, $password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die("Connection failed: " . $e->getMessage());
}

function getTableInfo($pdo, $tableName) {
	$stmt = $pdo->prepare("DESCRIBE $tableName");
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getForeignKeys($pdo, $tableName) {
	$stmt = $pdo->prepare("
		SELECT
			COLUMN_NAME,
			REFERENCED_TABLE_NAME,
			REFERENCED_COLUMN_NAME
		FROM
			INFORMATION_SCHEMA.KEY_COLUMN_USAGE
		WHERE
			TABLE_SCHEMA = :dbName AND
			TABLE_NAME = :tableName AND
			REFERENCED_TABLE_NAME IS NOT NULL
	");
	$stmt->execute(['dbName' => $pdo->query("SELECT DATABASE()")->fetchColumn(), 'tableName' => $tableName]);
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getIndexes($pdo, $tableName) {
	$stmt = $pdo->prepare("SHOW INDEX FROM $tableName");
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateClass($pdo, $tableName) {
	$tableInfo = getTableInfo($pdo, $tableName);
	$foreignKeys = getForeignKeys($pdo, $tableName);
	$indexes = getIndexes($pdo, $tableName);

	$className = ucfirst($tableName);
	$properties = [];
	$setters = [];
	$getters = [];
	$insertParams = [];
	$updateParams = [];
	$bindParams = [];
	$validations = [];

	foreach ($tableInfo as $column) {
		$columnName = $column['Field'];
		$type = $column['Type'];
		$null = $column['Null'];
		$key = $column['Key'];
		$default = $column['Default'];

		$properties[] = "protected \$$columnName;";
		$setters[] = generateSetter($columnName, $type, $null);
		$getters[] = generateGetter($columnName);

		if ($key !== 'PRI') {
			$insertParams[] = $columnName;
			$updateParams[] = "$columnName = :$columnName";
		}

		$bindParams[] = "\$stmt->bindValue(':$columnName', \$this->$columnName);";
		$validations[] = generateValidation($columnName, $type, $null);
	}

	$classContent = "<?php\n\nclass $className {\n";
	$classContent .= implode("\n	", $properties) . "\n\n";
	$classContent .= implode("\n", $setters) . "\n";
	$classContent .= implode("\n", $getters) . "\n";

	$classContent .= generateInsertMethod($tableName, $insertParams, $bindParams);
	$classContent .= generateUpdateMethod($tableName, $updateParams, $bindParams);
	$classContent .= generateDeleteMethod($tableName);
	$classContent .= generateSelectMethods($tableName, $indexes, $foreignKeys);
	$classContent .= generateValidateMethod($validations);

	$classContent .= "}\n";
	$dir = __DIR__.'/gen/'.$tableName;
	if(!is_dir($dir)) mkdir($dir, 0777, true);
	file_put_contents("{$dir}/{$className}.php", $classContent);
	echo "Generated {$dir}/{$className}.php\n";
}

function generateSetter($columnName, $type, $null) {
	$methodName = 'set' . ucfirst($columnName);
	return "
	public function $methodName(\$$columnName) {
		\$this->$columnName = \$$columnName;
		return \$this;
	}";
}

function generateGetter($columnName) {
	$methodName = 'get' . ucfirst($columnName);
	return "
	public function $methodName() {
		return \$this->$columnName;
	}";
}

function generateInsertMethod($tableName, $insertParams, $bindParams) {
	$insertColumns = implode(', ', $insertParams);
	$insertValues = ':' . implode(', :', $insertParams);
	$bindParamsStr = implode("\n		", $bindParams);

	return "
	public function insert(PDO \$pdo) {
		\$this->validate();
		\$sql = \"INSERT INTO $tableName ($insertColumns) VALUES ($insertValues)\";
		\$stmt = \$pdo->prepare(\$sql);
		$bindParamsStr
		return \$stmt->execute();
	}";
}

function generateUpdateMethod($tableName, $updateParams, $bindParams) {
	$updateStr = implode(', ', $updateParams);
	$bindParamsStr = implode("\n		", $bindParams);

	return "
	public function update(PDO \$pdo) {
		\$this->validate();
		\$sql = \"UPDATE $tableName SET $updateStr WHERE id = :id\";
		\$stmt = \$pdo->prepare(\$sql);
		$bindParamsStr
		return \$stmt->execute();
	}";
}

function generateDeleteMethod($tableName) {
	return "
	public function delete(PDO \$pdo) {
		\$sql = \"DELETE FROM $tableName WHERE id = :id\";
		\$stmt = \$pdo->prepare(\$sql);
		\$stmt->bindValue(':id', \$this->id);
		return \$stmt->execute();
	}";
}

function generateSelectMethods($tableName, $indexes, $foreignKeys) {
	$methods = "
	public static function findById(PDO \$pdo, \$id) {
		\$sql = \"SELECT * FROM $tableName WHERE id = :id\";
		\$stmt = \$pdo->prepare(\$sql);
		\$stmt->bindValue(':id', \$id);
		\$stmt->execute();
		return \$stmt->fetchObject(__CLASS__);
	}";

	foreach ($indexes as $index) {
		if ($index['Key_name'] !== 'PRIMARY') {
			$columnName = $index['Column_name'];
			$methodName = 'findBy' . ucfirst($columnName);
			$methods .= "
	public static function $methodName(PDO \$pdo, \$$columnName) {
		\$sql = \"SELECT * FROM $tableName WHERE $columnName = :$columnName\";
		\$stmt = \$pdo->prepare(\$sql);
		\$stmt->bindValue(':$columnName', \$$columnName);
		\$stmt->execute();
		return \$stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}";
		}
	}

	foreach ($foreignKeys as $fk) {
		$columnName = $fk['COLUMN_NAME'];
		$referencedTable = $fk['REFERENCED_TABLE_NAME'];
		$referencedColumn = $fk['REFERENCED_COLUMN_NAME'];
		$methodName = 'findBy' . ucfirst($referencedTable) . ucfirst($referencedColumn);
		$methods .= "
	public static function $methodName(PDO \$pdo, \$$referencedColumn) {
		\$sql = \"SELECT * FROM $tableName WHERE $columnName = :$referencedColumn\";
		\$stmt = \$pdo->prepare(\$sql);
		\$stmt->bindValue(':$referencedColumn', \$$referencedColumn);
		\$stmt->execute();
		return \$stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}";
	}

	return $methods;
}

function generateValidation($columnName, $type, $null) {
	$validation = "";
	if ($null === 'NO') {
		$validation .= "if (\$this->$columnName === null) throw new InvalidArgumentException('$columnName cannot be null');\n";
	}
	if (strpos($type, 'int') !== false) {
		$validation .= "if (!\is_int(\$this->$columnName)) throw new InvalidArgumentException('$columnName must be an integer');\n";
	} elseif (strpos($type, 'float') !== false || strpos($type, 'double') !== false || strpos($type, 'decimal') !== false) {
		$validation .= "if (!\is_numeric(\$this->$columnName)) throw new InvalidArgumentException('$columnName must be a number');\n";
	} elseif (strpos($type, 'date') !== false) {
		$validation .= "if (!\DateTime::createFromFormat('Y-m-d', \$this->$columnName)) throw new InvalidArgumentException('$columnName must be a valid date');\n";
	} elseif (strpos($type, 'varchar') !== false) {
		preg_match('/varchar$$(\d+)$$/', $type, $matches);
		if (isset($matches[1])) {
			$maxLength = $matches[1];
			$validation .= "if (strlen(\$this->$columnName) > $maxLength) throw new InvalidArgumentException('$columnName must not exceed $maxLength characters');\n";
		}
	}
	return $validation;
}

function generateValidateMethod($validations) {
	return "
	protected function validate() {
		" . implode("\n		", $validations) . "
	}";
}

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
	generateClass($pdo, $table);
}

echo "Class generation complete.\n";
