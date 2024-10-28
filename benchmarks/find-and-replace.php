<?php

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
		$PrintfTime = $this->benchmarkPrintf($iterations);
		$StrReplaceTime = $this->benchmarkStrReplace($iterations);
		$bbcodeTime = $this->benchmarkBBCode($iterations);
		$strtrTime = $this->benchmarkStrtr($iterations);
		$pregReplaceTime = $this->benchmarkPregReplace($iterations);

		printf("sprintf: %f seconds\n", 		$sprintfTime);
		printf("printf: %f seconds\n", 			$PrintfTime);
		printf("str_replace: %f seconds\n", $StrReplaceTime);
		printf("bbcode: %f seconds\n", 			$bbcodeTime);
		printf("strtr: %f seconds\n", 			$strtrTime);
		printf("preg_replace: %f seconds\n", $pregReplaceTime);
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
	 * Benchmark printf function with a complex string
	 *
	 * @param int $iterations
	 * @return float
	 */
	private function benchmarkPrintf(int $iterations): float
	{
		$start = microtime(true);

		for ($i = 0; $i < $iterations; $i++) {
			printf(
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
	 * Benchmark bbcode-like function with a complex string
	 *
	 * @param int $iterations
	 * @return float
	 */
	private function benchmarkBBCode(int $iterations): float
	{
		$start = microtime(true);

		for ($i = 0; $i < $iterations; $i++) {
			$this->bbcode(
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

	/**
	 * Benchmark bbcode-like function with a complex string
	 *
	 * @param int $iterations
	 * @return float
	 */
	private function benchmarkStrReplace(int $iterations): float
	{
		$start = microtime(true);

		for ($i = 0; $i < $iterations; $i++) {
			str_replace(
				['[name]', '[count]', '[balance]', '[date]', 	'[time]',],
				['Yohn', 		5, 					1234.56, '2024-10-27', '12:00 PM'],
				'Hello [name], you have [count] new messages, your balance is [balance], and your last login was on [date] at [time]',
			);
		}

		return microtime(true) - $start;
	}
	//array_keys($replacements), array_values($replacements), $template);

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

	/**
	 * Benchmark preg_replace function for replacement
	 *
	 * @param int $iterations
	 * @return float
	 */
	private function benchmarkPregReplace(int $iterations): float
	{
		$start = microtime(true);

		for ($i = 0; $i < $iterations; $i++) {
			preg_replace(
				['/\[name\]/', '/\[count\]/', '/\[balance\]/', '/\[date\]/', '/\[time\]/'],
				['Yohn', 5, 1234.56, '2024-10-27', '12:00 PM'],
				'Hello [name], you have [count] new messages, your balance is [balance], and your last login was on [date] at [time]'
			);
		}

		return microtime(true) - $start;
	}

	/**
	 * Simulate BBCode-like string replacement
	 *
	 * @param string $template
	 * @param array $replacements
	 * @return string
	 */
	private function bbcode(string $template, array $replacements): string
	{
		return str_replace(array_keys($replacements), array_values($replacements), $template);
	}
}

$benchmark = new Benchmark();
$benchmark->run();

/**
 * PHP 8.3.12
 * sprintf: 0.236711 seconds
 * strtr: 0.305862 seconds
 * preg_replace: 0.400507 seconds
 * bbcode: 0.458876 seconds
 * (didnt run printf in 8.3.12)
 *
 * PHP 8.3.8 for the rest below
 * strtr: 				0.080120 seconds
 * sprintf: 			0.117685 seconds
 * preg_replace: 	0.133060 seconds
 * bbcode: 				0.200995 seconds
 * printf: 				0.649218 seconds
 * 
 * Added str_replace in conjunction with keeping bbcode in there...
 * 
 * strtr: 			0.096684 seconds
 * sprintf: 		0.103677 seconds
 * str_replace: 0.112268 seconds
 * bbcode: 			0.185911 seconds
 * preg_replace: 0.236513 seconds
 * printf: 			1.242448 seconds
 * 
 * sprintf: 0.083905 seconds
 * strtr: 0.086414 seconds
 * str_replace: 0.115080 seconds
 * preg_replace: 0.132141 seconds
 * bbcode: 0.258569 seconds
 * printf: 0.710810 seconds
 */
