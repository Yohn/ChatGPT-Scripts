<?php

class Validator {
	public static function validate($validations) {
		foreach ($validations as $validation) {
			$validation();
		}
	}

	public static function generateValidation($columnName, $type, $null) {
		$validation = "";
		if ($null === 'NO') {
			$validation .= "function() { if (\$this->$columnName === null) throw new InvalidArgumentException('$columnName cannot be null'); }";
		}
		if (strpos($type, 'int') !== false) {
			$validation .= ($validation ? ", " : "") . "function() { if (!\is_int(\$this->$columnName)) throw new InvalidArgumentException('$columnName must be an integer'); }";
		} elseif (strpos($type, 'float') !== false || strpos($type, 'double') !== false || strpos($type, 'decimal') !== false) {
			$validation .= ($validation ? ", " : "") . "function() { if (!\is_numeric(\$this->$columnName)) throw new InvalidArgumentException('$columnName must be a number'); }";
		} elseif (strpos($type, 'date') !== false) {
			$validation .= ($validation ? ", " : "") . "function() { if (!\DateTime::createFromFormat('Y-m-d', \$this->$columnName)) throw new InvalidArgumentException('$columnName must be a valid date'); }";
		} elseif (strpos($type, 'varchar') !== false) {
			preg_match('/varchar$$(\d+)$$/', $type, $matches);
			if (isset($matches[1])) {
				$maxLength = $matches[1];
				$validation .= ($validation ? ", " : "") . "function() { if (strlen(\$this->$columnName) > $maxLength) throw new InvalidArgumentException('$columnName must not exceed $maxLength characters'); }";
			}
		}
		return $validation;
	}
}