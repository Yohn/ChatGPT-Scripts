<?php

namespace Data;

use JW3B\Helpful\Files;

class FileCache
{
	private string $style;
	private string $tabs_or_space;
	private string $dir_path;
	private string $real_file;
	private string $file_data;

	/**
	 * Constructor
	 *
	 * @param string $dataname
	 * @param string $style
	 * @param string $tabs_or_space
	 */
	public function __construct(string $dataname, string $style = 'lines', string $tabs_or_space = "\t")
	{
		$this->style = $style;
		$this->tabs_or_space = $tabs_or_space;
		$this->dir_path = str_ends_with($dataname, '/') ? $dataname : $dataname . '/';
		$ex = explode('/', $this->dir_path);
		$prev = '';
		foreach ($ex as $dir) {
			Files::mk_dir_writable($prev . $dir);
			$prev = $dir . '/';
		}
	}

	/**
	 * Get file content
	 *
	 * @param string $path
	 * @return array|bool
	 */
	public function get_file(string $path): array|bool
	{
		$real_file = $this->check_path($path);
		if (is_file($real_file)) {
			return include($real_file);
		} else {
			return false;
		}
	}

	/**
	 * Get line file content
	 *
	 * @param string $path
	 * @return array|bool
	 */
	public function get_line_file(string $path): array|bool
	{
		$real_file = $this->check_path($path);
		if (is_file(filename: $real_file)) {
			return file(filename: $real_file);
		} else {
			return false;
		}
	}

	/**
	 * Save data to file
	 *
	 * @param string $path
	 * @param string|array $data
	 * @param string $how_to_update
	 * @return bool
	 */
	public function save(string $path, string|array $data, string $how_to_update = 'add'): bool
	{
		$this->real_file = $this->check_path($path);
		if ($this->should_update_file($this->real_file)) {
			return $this->set_up_file(
				is_array($data) ? $data : [$data],
				strtolower($how_to_update)
			)->save_file('w');
		}
		return false;
	}

	/**
	 * Check if the file should be updated
	 *
	 * @param string $file
	 * @return bool
	 */
	private function should_update_file(string $file): bool
	{
		if (is_file($file)) {
			$last_modified = filemtime($file);
			if ($last_modified !== false) {
				$time_diff = time() - $last_modified;
				if ($time_diff < 43200) { // 12 hours = 43200 seconds
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Check and create necessary directories
	 *
	 * @param string $path
	 * @return string
	 */
	private function check_path(string $path): string
	{
		$dirs = explode('/', $path);
		$total = count($dirs);
		$fileName = $dirs[$total - 1];
		if ($total > 1) {
			$prev = '';
			for ($i = 0; $i < $total; $i++) {
				if ($dirs[$i] != $fileName) {
					Files::mk_dir_writable($this->dir_path . $prev . $dirs[$i]);
					$prev .= $dirs[$i] . '/';
				}
			}
		}
		return $this->dir_path . $path . '.php';
	}

	/**
	 * Set up file data
	 *
	 * @param array $data
	 * @param string $how_to_update
	 * @return static
	 */
	private function set_up_file(array $data, string $how_to_update): static
	{
		if ($this->style == 'lines') {
			if (is_file($this->real_file)) {
				$bkup = substr_replace($this->real_file, 'backup.php', -3, 3);
				copy($this->real_file, $bkup);
				$found = file($this->real_file);
				$found[] = $this->set_up_line_file($data);
				$this->file_data = implode(PHP_EOL, $found);
			} else {
				$this->file_data = $this->set_up_line_file($data) . PHP_EOL;
			}
		} else if ($this->style == 'return') {
			if (is_file($this->real_file)) {
				$bkup = substr_replace($this->real_file, 'backup.php', -3, 3);
				copy($this->real_file, $bkup);
				$found = include($this->real_file);
				if ($how_to_update == 'replace') {
					// replacing the whole file with new data
				} else if ($how_to_update == 'add') {
					$data = array_merge_recursive($found, $data);
				} else if ($how_to_update == 'update') {
					$data = array_merge($found, $data);
				}
			}
			$this->file_data = "<?php\n\nreturn [\n" . $this->setup_return_ary($data, 1) . "\n];";
		} else {
			throw new \ErrorException('FileWriting style "' . $this->style . '" is not a valid acceptable style type. Currently only "lines" and "return" are the allowed values', 10, E_ERROR);
		}
		return $this;
	}

	/**
	 * Set up return array
	 *
	 * @param array $data
	 * @param int $tabs
	 * @return string
	 */
	private function setup_return_ary(array $data, int $tabs = 1): string
	{
		$file = '';
		$t = $this->tabs($tabs);
		$total = count($data);
		$c = 0;
		foreach ($data as $k => $v) {
			$c++;
			$add = $c < $total ? ',' : '';
			$file .= $t . '\'' . $k . '\' => ' . $this->check_value_for_return($v, $tabs) . $add . "\n";
		}
		return $file;
	}

	/**
	 * Check value for return
	 *
	 * @param mixed $str
	 * @param int $tabs
	 * @return string
	 */
	private function check_value_for_return(mixed $str, int $tabs): string
	{
		if (is_array($str)) {
			return "[\n" . $this->setup_return_ary($str, $tabs + 1) . $this->tabs($tabs) . "]";
		}
		return var_export($str, true);
	}

	/**
	 * Format tabs
	 *
	 * @param int $n
	 * @return string
	 */
	private function tabs(int $n = 1): string
	{
		return str_repeat($this->tabs_or_space, $n);
	}

	/**
	 * Set up line file content
	 *
	 * @param array $ary
	 * @return string
	 */
	private function set_up_line_file(array $ary): string
	{
		$return = '';
		foreach ($ary as $v) {
			$return .= is_array($v) ? implode("\n", $v) . "\n" : $v . "\n";
		}
		return trim($return);
	}

	/**
	 * Save file content
	 *
	 * @param string $type
	 * @return bool
	 */
	private function save_file(string $type = 'w'): bool
	{
		return file_put_contents($this->real_file, $this->file_data, LOCK_EX) !== false;
	}
}
