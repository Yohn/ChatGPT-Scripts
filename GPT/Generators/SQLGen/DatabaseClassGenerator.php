<?php
/**
 * Class DatabaseClassGenerator
 * Generates PHP classes for database tables with CRUD operations and validation.
 */
class DatabaseClassGenerator {
	private $pdo;
	private $dbName;

	/**
	 * DatabaseClassGenerator constructor.
	 * @param string $dbName Database name
	 * @param string $username Database username
	 * @param string $password Database password
	 */
	public function __construct($dbName, $username, $password) {
		$this->dbName = $dbName;
		try {
			$this->pdo = new PDO("mysql:host=localhost;dbname=$dbName", $username, $password);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			die("Connection failed: " . $e->getMessage());
		}
	}

	/**
	 * Generate classes for all tables in the database.
	 */
	public function generateClasses() {
		$tables = $this->pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

		foreach ($tables as $table) {
			$this->generateClass($table);
		}

		echo "Class generation complete.\n";
	}

	/**
	 * Generate a class for a specific table.
	 * @param string $tableName Name of the table
	 */
	private function generateClass($tableName) {
		$tableInfo = $this->getTableInfo($tableName);
		$foreignKeys = $this->getForeignKeys($tableName);
		$indexes = $this->getIndexes($tableName);
		$triggers = $this->getTriggers($tableName);

		$className = $this->toPascalCase($tableName);
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
			$extra = $column['Extra'];

			$hasUpdateTrigger = $this->hasUpdateTrigger($triggers, $columnName);
			if ($hasUpdateTrigger) {
				continue;
			}

			$properties[] = $this->generateProperty($columnName, $type);
			$setters[] = $this->generateSetter($columnName, $type, $null);
			$getters[] = $this->generateGetter($columnName, $type);

			if ($key !== 'PRI' && $extra !== 'auto_increment' && !$this->hasUpdateTrigger($triggers, $columnName)) {
				$insertParams[] = $columnName;
				$updateParams[] = "$columnName = :$columnName";
			}

			$bindParams[] = "\$stmt->bindValue(':$columnName', \$this->$columnName);";
			$validations[] = $this->generateValidation($columnName, $type, $null);
		}

		$classContent = "<?php\n\nrequire_once 'Validator.php';\n\n/**\n * Class $className\n * Represents the $tableName table in the database.\n */\nclass $className {\n";
		$classContent .= implode("\n", $properties) . "\n\n";
		$classContent .= implode("\n", $setters) . "\n";
		$classContent .= implode("\n", $getters) . "\n";

		$classContent .= $this->generateInsertMethod($tableName, $insertParams, $bindParams);
		$classContent .= $this->generateUpdateMethod($tableName, $updateParams, $bindParams);
		$classContent .= $this->generateDeleteMethod($tableName);
		$classContent .= $this->generateSelectMethods($tableName, $indexes, $foreignKeys);
		$classContent .= $this->generateValidateMethod($validations);

		$classContent .= "}\n";

		$dir = basename(__DIR__)."/AI/".$this->dbName."/".$tableName;
		if(!is_dir($dir)) mkdir($dir, 0777, true);
		file_put_contents("{$dir}/{$className}.php", $classContent);
		echo "Generated {$className}.php\n";
	}

	/**
	 * Get table information.
	 * @param string $tableName Name of the table
	 * @return array Table information
	 */
	private function getTableInfo($tableName) {
		$stmt = $this->pdo->prepare("DESCRIBE $tableName");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Get foreign keys for a table.
	 * @param string $tableName Name of the table
	 * @return array Foreign key information
	 */
	private function getForeignKeys($tableName) {
		$stmt = $this->pdo->prepare("
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
		$stmt->execute(['dbName' => $this->dbName, 'tableName' => $tableName]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Get indexes for a table.
	 * @param string $tableName Name of the table
	 * @return array Index information
	 */
	private function getIndexes($tableName) {
		$stmt = $this->pdo->prepare("SHOW INDEX FROM $tableName");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Get triggers for a table.
	 * @param string $tableName Name of the table
	 * @return array Trigger information
	 */
	private function getTriggers($tableName) {
		$stmt = $this->pdo->prepare("SHOW TRIGGERS WHERE `Table` = :tableName");
		$stmt->execute(['tableName' => $tableName]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Check if a column has an update trigger.
	 * @param array $triggers Trigger information
	 * @param string $columnName Name of the column
	 * @return bool True if the column has an update trigger, false otherwise
	 */
	private function hasUpdateTrigger($triggers, $columnName) {
		foreach ($triggers as $trigger) {
			if (
				(strpos($trigger['Event'], 'INSERT') !== false || strpos($trigger['Event'], 'UPDATE') !== false) &&
				strpos($trigger['Statement'], "NEW.$columnName") !== false
			) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Generate a property declaration.
	 * @param string $columnName Name of the column
	 * @param string $type Type of the column
	 * @return string Property declaration
	 */
	private function generateProperty($columnName, $type) {
		$phpType = $this->mysqlToPhpType($type);
		return "\t/** @var $phpType */\n\tprotected \$$columnName;";
	}

	/**
	 * Generate a setter method.
	 * @param string $columnName Name of the column
	 * @param string $type Type of the column
	 * @param string $null Whether the column can be null
	 * @return string Setter method
	 */
	private function generateSetter($columnName, $type, $null) {
		$methodName = 'set' . $this->toPascalCase($columnName);
		$phpType = $this->mysqlToPhpType($type);
		return "
	/**
	 * Set the value of $columnName
	 * @param $phpType \$$columnName
	 * @return \$this
	 */
	public function $methodName(\$$columnName) {
		\$this->$columnName = \$$columnName;
		return \$this;
	}";
	}

	/**
	 * Generate a getter method.
	 * @param string $columnName Name of the column
	 * @param string $type Type of the column
	 * @return string Getter method
	 */
	private function generateGetter($columnName, $type) {
		$methodName = 'get' . $this->toPascalCase($columnName);
		$phpType = $this->mysqlToPhpType($type);
		return "
	/**
	 * Get the value of $columnName
	 * @return $phpType
	 */
	public function $methodName() {
		return \$this->$columnName;
	}";
	}

	/**
	 * Generate the insert method.
	 * @param string $tableName Name of the table
	 * @param array $insertParams Parameters to insert
	 * @param array $bindParams Parameters to bind
	 * @return string Insert method
	 */
	private function generateInsertMethod($tableName, $insertParams, $bindParams) {
		$insertColumns = implode(', ', $insertParams);
		$insertValues = ':' . implode(', :', $insertParams);
		$bindParamsStr = implode("\n\t\t", $bindParams);

		return "
	/**
	 * Insert a new record into the database
	 * @param PDO \$pdo PDO instance
	 * @return bool True if the insert was successful, false otherwise
	 */
	public function insert(PDO \$pdo) {
		\$this->validate();
		\$sql = \"INSERT INTO $tableName ($insertColumns) VALUES ($insertValues)\";
		\$stmt = \$pdo->prepare(\$sql);
		$bindParamsStr
		return \$stmt->execute();
	}";
	}

	/**
	 * Generate the update method.
	 * @param string $tableName Name of the table
	 * @param array $updateParams Parameters to update
	 * @param array $bindParams Parameters to bind
	 * @return string Update method
	 */
	private function generateUpdateMethod($tableName, $updateParams, $bindParams) {
		$updateStr = implode(', ', $updateParams);
		$bindParamsStr = implode("\n\t\t", $bindParams);

		return "
	/**
	 * Update an existing record in the database
	 * @param PDO \$pdo PDO instance
	 * @return bool True if the update was successful, false otherwise
	 */
	public function update(PDO \$pdo) {
		\$this->validate();
		\$sql = \"UPDATE $tableName SET $updateStr WHERE id = :id\";
		\$stmt = \$pdo->prepare(\$sql);
		$bindParamsStr
		return \$stmt->execute();
	}";
	}

	/**
	 * Generate the delete method.
	 * @param string $tableName Name of the table
	 * @return string Delete method
	 */
	private function generateDeleteMethod($tableName) {
		return "
	/**
	 * Delete the record from the database
	 * @param PDO \$pdo PDO instance
	 * @return bool True if the delete was successful, false otherwise
	 */
	public function delete(PDO \$pdo) {
		\$sql = \"DELETE FROM $tableName WHERE id = :id\";
		\$stmt = \$pdo->prepare(\$sql);
		\$stmt->bindValue(':id', \$this->id);
		return \$stmt->execute();
	}";
	}

	/**
	 * Generate select methods.
	 * @param string $tableName Name of the table
	 * @param array $indexes Index information
	 * @param array $foreignKeys Foreign key information
	 * @return string Select methods
	 */
	private function generateSelectMethods($tableName, $indexes, $foreignKeys) {
		$methods = "
	/**
	 * Find a record by its ID
	 * @param PDO \$pdo PDO instance
	 * @param int \$id ID to search for
	 * @return self|null The found object or null if not found
	 */
	public static function findById(PDO \$pdo, \$id) {
		\$sql = \"SELECT * FROM $tableName WHERE id = :id\";
		\$stmt = \$pdo->prepare(\$sql);
		\$stmt->bindValue(':id', \$id);
		\$stmt->execute();
		return \$stmt->fetchObject(__CLASS__);
	}";

		$generatedMethods = ['findById' => true];

		foreach ($indexes as $index) {
			if ($index['Key_name'] !== 'PRIMARY' && !isset($generatedMethods[$index['Column_name']])) {
				$columnName = $index['Column_name'];
				$methodName = 'findBy' . $this->toPascalCase($columnName);
				if (!isset($generatedMethods[$methodName])) {
					$methods .= $this->generateFindByMethod($tableName, $columnName, $methodName);
					$generatedMethods[$methodName] = true;
				}
			}
		}

		foreach ($foreignKeys as $fk) {
			$columnName = $fk['COLUMN_NAME'];
			$referencedTable = $fk['REFERENCED_TABLE_NAME'];
			$referencedColumn = $fk['REFERENCED_COLUMN_NAME'];
			$methodName = 'findBy' . $this->toPascalCase($referencedTable) . $this->toPascalCase($referencedColumn);
			if (!isset($generatedMethods[$methodName])) {
				$methods .= $this->generateFindByMethod($tableName, $columnName, $methodName, $referencedColumn);
				$generatedMethods[$methodName] = true;
			}
		}

		return $methods;
	}

	/**
	 * Generate a findBy method for a specific column
	 * @param string $tableName Name of the table
	 * @param string $columnName Name of the column to search by
	 * @param string $methodName Name of the method to generate
	 * @param string|null $paramName Optional parameter name if different from column name
	 * @return string Generated method
	 */
	private function generateFindByMethod($tableName, $columnName, $methodName, $paramName = null) {
		$paramName = $paramName ?? $columnName;
		return "
	/**
	 * Find records by $columnName
	 * @param PDO \$pdo PDO instance
	 * @param mixed \$$paramName Value to search for
	 * @return self[] Array of found objects
	 */
	public static function $methodName(PDO \$pdo, \$$paramName) {
		\$sql = \"SELECT * FROM $tableName WHERE $columnName = :$paramName\";
		\$stmt = \$pdo->prepare(\$sql);
		\$stmt->bindValue(':$paramName', \$$paramName);
		\$stmt->execute();
		return \$stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}";
	}

	/**
	 * Generate the validate method.
	 * @param array $validations Validation rules
	 * @return string Validate method
	 */
	private function generateValidateMethod($validations) {
		return "
	/**
	 * Validate the object's properties
	 * @throws InvalidArgumentException if validation fails
	 */
	protected function validate()
	{
		Validator::validate([
			" . implode(",\n\t\t\t", $validations) . "
		]);
	}";
	}

	/**
	 * Generate a validation rule for a column.
	 * @param string $columnName Name of the column
	 * @param string $type Type of the column
	 * @param string $null Whether the column can be null
	 * @return string Validation rule
	 */
	private function generateValidation($columnName, $type, $null) {
		$validation = "";
		if ($null === 'NO') {
			$validation .= "function() { if (\$this->$columnName === null) throw new InvalidArgumentException('$columnName cannot be null'); }";
		}
		if (strpos($type, 'int') !== false) {
			$validation .= ($validation ? ", " : "") . "function() { if (\$this->$columnName !== null && !filter_var(\$this->$columnName, FILTER_VALIDATE_INT)) throw new InvalidArgumentException('$columnName must be an integer'); }";
		} elseif (strpos($type, 'float') !== false || strpos($type, 'double') !== false || strpos($type, 'decimal') !== false) {
			$validation .= ($validation ? ", " : "") . "function() { if (\$this->$columnName !== null && !is_numeric(\$this->$columnName)) throw new InvalidArgumentException('$columnName must be a number'); }";
		} elseif (strpos($type, 'date') !== false) {
			$validation .= ($validation ? ", " : "") . "function() { if (\$this->$columnName !== null && !\DateTime::createFromFormat('Y-m-d', \$this->$columnName)) throw new InvalidArgumentException('$columnName must be a valid date in Y-m-d format'); }";
		} elseif (strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false) {
			$validation .= ($validation ? ", " : "") . "function() { if (\$this->$columnName !== null && !\DateTime::createFromFormat('Y-m-d H:i:s', \$this->$columnName)) throw new InvalidArgumentException('$columnName must be a valid datetime in Y-m-d H:i:s format'); }";
		} elseif (strpos($type, 'time') !== false) {
			$validation .= ($validation ? ", " : "") . "function() { if (\$this->$columnName !== null && !\DateTime::createFromFormat('H:i:s', \$this->$columnName)) throw new InvalidArgumentException('$columnName must be a valid time in H:i:s format'); }";
		} elseif (strpos($type, 'varchar') !== false || strpos($type, 'char') !== false || strpos($type, 'text') !== false) {
			preg_match('/$$(\d+)$$/', $type, $matches);
			if (isset($matches[1])) {
				$maxLength = $matches[1];
				$validation .= ($validation ? ", " : "") . "function() { if (\$this->$columnName !== null && strlen(\$this->$columnName) > $maxLength) throw new InvalidArgumentException('$columnName must not exceed $maxLength characters'); }";
			}
		} elseif (strpos($type, 'enum') !== false) {
			preg_match('/enum$$(.*?)$$/', $type, $matches);
			if (isset($matches[1])) {
				$allowedValues = explode(',', str_replace("'", '', $matches[1]));
				$allowedValuesStr = implode("', '", $allowedValues);
				$validation .= ($validation ? ", " : "") . "function() { if (\$this->$columnName !== null && !in_array(\$this->$columnName, ['$allowedValuesStr'])) throw new InvalidArgumentException('$columnName must be one of: $allowedValuesStr'); }";
			}
		}
		return $validation;
	}

	/**
	 * Convert MySQL type to PHP type.
	 * @param string $mysqlType MySQL type
	 * @return string PHP type
	 */
	private function mysqlToPhpType($mysqlType) {
		if (strpos($mysqlType, 'int') !== false) {
			return 'int';
		} elseif (strpos($mysqlType, 'float') !== false || strpos($mysqlType, 'double') !== false || strpos($mysqlType, 'decimal') !== false) {
			return 'float';
		} elseif (strpos($mysqlType, 'datetime') !== false || strpos($mysqlType, 'timestamp') !== false) {
			return 'string';
		} elseif (strpos($mysqlType, 'date') !== false) {
			return 'string';
		} elseif (strpos($mysqlType, 'time') !== false) {
			return 'string';
		} else {
			return 'string';
		}
	}

	/**
	 * Convert a string to PascalCase.
	 * @param string $string String to convert
	 * @return string Converted string
	 */
	private function toPascalCase($string) {
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
	}
}