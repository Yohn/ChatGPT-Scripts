<?php
/**
 * Benchmar Class
 * ChatGPT made this class for my find and replace benchmarks, and I like how easy it is to use, so I wanted to save it.
 */
class Benchmark
{
	private string $file;
	/**
	 * Run the benchmark for sprintf, bbcode, and other replacement methods
	 *
	 * @param string $file path to a file to test for benchmarking
	 * @param int $iterations
	 * @return void
	 */
	public function run(string $file, int $iterations = 5000): void
	{
		$this->file = $file;
		$fileTime = $this->benchmarkFile($iterations);
		$fileGetContentsTime = $this->benchmarkFileGetContents($iterations);
		$FopenTime = $this->benchmarkFopen($iterations);
		//$SplFileObjectTime = $this->benchmarkSplFileObject($iterations);

		echo strtr("file: ;time; seconds\n", 							[';time;' => $fileTime]);
		echo strtr("file_get_contents: ;time; seconds\n", [';time;' => $fileGetContentsTime]);
		echo strtr("fopen: ;time; seconds\n", [';time;' => $FopenTime]);
		// SplFileObject was just to slow. Almost 10x slower.
		//echo strtr("SplFileObject: ;time; seconds\n", [';time;' => $SplFileObjectTime]);
  }

  /**
	 * Benchmark file function with a medium sized file
	 *
	 * @param int $iterations
	 * @return float
	 */
	private function benchmarkFile(int $iterations): float
	{
		$start = microtime(true);
		for ($i = 0; $i < $iterations; $i++) {
			//! Within the foreach look, put in your code you want to test, these are just examples
			$data = file($this->file);
		}
		return microtime(true) - $start;
	}

	/**
	 * Benchmark file_get_contents function with a medium sized file
	 *
	 * @param int $iterations
	 * @return float
	 */
	private function benchmarkFileGetContents(int $iterations): float
	{
		$start = microtime(true);
		for ($i = 0; $i < $iterations; $i++) {
			//! Within the foreach look, put in your code you want to test, these are just examples
			$data = file_get_contents($this->file);
		}
		return microtime(true) - $start;
	}

	/**
	 * Benchmark fopen function with a medium sized file
	 *
	 * @param int $iterations
	 * @return float
	 */
	private function benchmarkFopen(int $iterations): float
	{
		$start = microtime(true);
		for ($i = 0; $i < $iterations; $i++) {
			//! Within the foreach look, put in your code you want to test, these are just examples
			$fp = fopen($this->file, 'r');
			$contents = fread($fp, filesize($this->file));
			fclose($fp);
		}
		return microtime(true) - $start;
	}

	/**
	 * Benchmark SplFileObject class with a medium sized file
	 *
	 * @param int $iterations
	 * @return float
	 */
	private function benchmarkSplFileObject(int $iterations): float
	{
		$start = microtime(true);
		for ($i = 0; $i < $iterations; $i++) {
			//! Within the foreach look, put in your code you want to test, these are just examples
			$file = new SplFileObject($this->file, "r");
			// this way did not make a difference.
			//while (!$file->eof()) {
			//	$buffer = $file->current();
			//	// Do something with $buffer ...
			//	$file->next();
			//}
			// like 7 times slower..
			//while (!$file->eof()) {
			//	$line = $file->fgets();
			//}
		}
		return microtime(true) - $start;
	}
}

$benchmark = new Benchmark();
$benchmark->run('COLUMNSchemaAry.php');


/**
 * Test 1
 * file: 0.18773698806763 seconds
 * file_get_contents: 0.12050819396973 seconds
 * fopen: 0.18622612953186 seconds
 * SplFileObject: 0.82062888145447 seconds
 *
 * Test 2
 * file: 0.93733096122742 seconds
 * file_get_contents: 0.58897614479065 seconds
 * fopen: 0.60636901855469 seconds
 *
 * Test 3
 * file: 0.97879195213318 seconds
 * file_get_contents: 0.67527604103088 seconds
 * fopen: 0.59705710411072 seconds
 *
 * Test 4
 * file: 0.89987015724182 seconds
 * file_get_contents: 0.63176512718201 seconds
 * fopen: 0.65859198570251 seconds
 *
 * Test 5
 * file: 0.94340491294861 seconds
 * file_get_contents: 0.62075114250183 seconds
 * fopen: 0.57977890968323 seconds
 */
