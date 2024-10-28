<?php
/**
 * Benchmar Class
 * ChatGPT made this class for my find and replace benchmarks, and I like how easy it is to use, so I wanted to save it.
 */
class Benchmark
{
	/**
	 * Run the benchmark for sprintf, bbcode, and other replacement methods
	 *
	 * @param int $iterations
	 * @return void
	 */
	public function run(int $iterations = 100000): void
	{
		$sprintfTime = $this->benchmarkSprintf($iterations);
		$strtrTime = $this->benchmarkStrtr($iterations);
    
		echo strtr("sprintf: ;time; seconds\n", 		[';time;' => $sprintfTime]);
		echo strtr("strtr: ;time; seconds\n", 			[';time;' => $strtrTime]);
  }
  
  /**
	 * Benchmark sprintf function with a complex string
	 *
	 * @param int $iterations
	 * @return float
	 */
	private function benchmarkSprintf(int $iterations): float
	{
		$start = microtime(true);
		for ($i = 0; $i < $iterations; $i++) {
      //! Within the foreach look, put in your code you want to test, these are just examples
			sprintf(
				'Hello %s, you have %d new messages, your balance is %f, and your last login was on %s at %s',
				'Yohn',
				5,
				1234.56,
				'2024-10-27',
				'12:00 PM'
			);
		}
		return microtime(true) - $start;
	}
  
	/**
	 * Benchmark strtr function for replacement
	 *
	 * @param int $iterations
	 * @return float
	 */
	private function benchmarkStrtr(int $iterations): float
	{
		$start = microtime(true);
		for ($i = 0; $i < $iterations; $i++) {
      //! Within the foreach look, put in your code you want to test, these are just examples
			strtr(
				'Hello [name], you have [count] new messages, your balance is [balance], and your last login was on [date] at [time]',
				[
					'[name]' => 'Yohn',
					'[count]' => 5,
					'[balance]' => 1234.56,
					'[date]' => '2024-10-27',
					'[time]' => '12:00 PM'
				]
			);
		}
		return microtime(true) - $start;
	}
}

$benchmark = new Benchmark();
$benchmark->run();
