<?php

// Function to extract SQL statements and variables
function extractSQL($phpCode) {
	// Pattern to match SQL statements
	$sqlPattern = '/\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b/si';
	// Pattern to match variables in SQL statements
	$variablePattern = '/(\$[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)/';

	// Array to hold all matches
	$result = [];

	// Split the PHP code into lines
	$lines = explode("\n", $phpCode);

	// Iterate through each line
	foreach ($lines as $line) {
		// Check if the line contains an SQL statement
		if (preg_match($sqlPattern, $line)) {
			// Extract variables from the SQL statement
			if (preg_match_all($variablePattern, $line, $matches, PREG_OFFSET_CAPTURE)) {
				foreach ($matches[0] as $match) {
					$var = $match[0];  // The variable itself
					$offset = $match[1];  // The position of the variable in the line

					// Get the string before and after the variable
					$beforeVar = substr($line, 0, $offset);
					$afterVar = substr($line, $offset + strlen($var));

					// Add to the result array
					$result[] = [
						'before' => $beforeVar,
						'variable' => $var,
						'after' => $afterVar
					];
				}
			}
		}
	}

	return $result;
}

// Sample PHP code containing SQL statements
/*
$phpCode = <<<PHP
<?php
\$id = 1;
\$name = 'John';
\$sql = "SELECT * FROM users WHERE id = \$id AND name = '\$name'";
\$query = "INSERT INTO users (id, name) VALUES (\$id, '\$name')";
?>
PHP;
*/


$phpCode = file_get_contents(__DIR__.'/../../oldCode/classes/Report.class.php');
// Execute the function
$extracted = extractSQL($phpCode);

// Output the result
print_r($extracted);

?>
