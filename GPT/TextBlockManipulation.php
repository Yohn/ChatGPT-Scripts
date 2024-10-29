<?php

/**
 * Class TextBlockManipulation
 * 
 * This class performs manipulations on text blocks based on specified 
 * characters and actions. It allows for finding blocks of text defined 
 * by a search character, applying a callable function to modify the text 
 * within those blocks, and optionally removing the search character 
 * after manipulation.
 */
class TextBlockManipulation {
	private string $searchChar;
	private mixed $action;
	private bool $removeSearchChar;

	/**
	 * TextBlockManipulation constructor.
	 *
	 * @param string $searchChar The character that defines the boundaries of 
	 *              the text blocks to be manipulated.
	 * @param mixed $action The function to apply to the text 
	 *							between the search characters. It can be a callable 
	 *							function or a predefined PHP function.
	 * @param bool $removeSearchChar Indicates whether to remove the search 
	 *              character after manipulation. Default is false.
	 * @throws InvalidArgumentException If the action parameter is not callable.
	 */
	public function __construct(string $searchChar, mixed $action, bool $removeSearchChar = false) {
		$this->searchChar = $searchChar;

		// Check if $action is a callable function or a string
		if (is_callable($action) || function_exists($action)) {
			$this->action = $action;
		} else {
			throw new InvalidArgumentException('Action must be a valid callable function or predefined PHP function.');
		}

		// Set removeSearchChar flag
		$this->removeSearchChar = (bool) $removeSearchChar;
	}

	/**
	 * Manipulates the input string based on the defined search character 
	 * and action.
	 *
	 * This method splits the input string into lines, applies a regex 
	 * pattern to find text blocks defined by the search character, and 
	 * modifies those blocks with the specified action.
	 *
	 * @param string $str The input string which contains text blocks to be 
	 *                    manipulated.
	 * 
	 * @return string The manipulated string with modifications applied to 
	 *                the defined text blocks.
	 */
	public function manipulate(string $str): string {
		$lines = explode("\n", $str); // Split the input string into lines.
		$newLines = '';
		foreach ($lines as $line) {
			// Construct regex pattern dynamically based on search character
			$pattern = '/' . preg_quote($this->searchChar, '/') . '([^' . preg_quote($this->searchChar, '/') . ']+)' . preg_quote($this->searchChar, '/') . '/';

			// Determine action to perform based on $this->action
			$newLine = preg_replace_callback(
				$pattern,
				function($matches) {
				$action = $this->action;
				$modifiedText = $action($matches[1]);
				return $this->removeSearchChar ? $modifiedText : $this->searchChar . $modifiedText . $this->searchChar;
				},
				$line
			);

			$newLines .= rtrim($newLine) . "\n"; // Append the manipulated line with a newline and remove trailing spaces.
		}
		return rtrim($newLines); // Return the manipulated lines as a single string and remove trailing spaces.
	}
}

// Example usage:
// Using a predefined PHP function 'strtolower'
$manipulator1 = new TextBlockManipulation(':|:', 'strtolower', true);
$str1 = ":|:Some:|: example text to :|:mAnIpUlAtE:|:.";
echo $manipulator1->manipulate($str1).PHP_EOL.PHP_EOL;
// Output: some example text to manipulate.

// Using a custom function to convert text to uppercase and keeping searchChar after manipulation
$manipulator2 = new TextBlockManipulation('^', function($valueBetweenOurSearchChars) {
	// Custom action example: Convert to uppercase
	return strtoupper($valueBetweenOurSearchChars);
}, false);

$str2 = "Some ^example^ text ^to^ manipulate.";
echo $manipulator2->manipulate($str2);
// Output: Some ^EXAMPLE^ text ^TO^ manipulate.
