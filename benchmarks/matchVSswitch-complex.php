<?php

function complexFunction($input) {
    // Simulate a computationally expensive operation
    return $input * 2 + sin($input) - cos($input);
}

function benchmarkComplexSwitch($iterations) {
    $result = 0;
    for ($i = 0; $i < $iterations; $i++) {
        $case = rand(0, 999);
        switch ($case) {
            case 0: $result += 1; break;
            case 1: $result += 2; break;
            case 2: $result += 3; break;
            // [Omitted: Add cases up to 997]
            case 998:
                switch (true) {
                    case $i % 2 == 0 && $case < 500: $result += complexFunction($case); break;
                    case $i % 2 != 0 || $case >= 500: $result += complexFunction($case) / 2; break;
                    default: $result += 0; break;
                }
                break;
            case 999:
                switch (rand(0, 9)) {
                    case 0: $result += 10; break;
                    case 1: $result += 20; break;
                    case 2: $result += 30; break;
                    case 3: $result += 40; break;
                    // [Omitted: Add cases up to 8]
                    case 9: $result += 100; break;
                    default: $result += 0; break;
                }
                break;
            default: $result += 0; break;
        }
    }
    return $result;
}

function benchmarkComplexMatch($iterations) {
    $result = 0;
    for ($i = 0; $i < $iterations; $i++) {
        $case = rand(0, 999);
        $result += match ($case) {
            0 => 1,
            1 => 2,
            2 => 3,
            // [Omitted: Add cases up to 997]
            998 => match (true) {
                $i % 2 == 0 && $case < 500 => complexFunction($case),
                $i % 2 != 0 || $case >= 500 => complexFunction($case) / 2,
                default => 0,
            },
            999 => match (rand(0, 9)) {
                0 => 10,
                1 => 20,
                2 => 30,
                3 => 40,
                // [Omitted: Add cases up to 8]
                9 => 100,
                default => 0,
            },
            default => 0,
        };
    }
    return $result;
}

// Number of iterations for the benchmark
$iterations = 1000000;

// Benchmark Complex Switch
$startTime = microtime(true);
benchmarkComplexSwitch($iterations);
$endTime = microtime(true);
$complexSwitchTime = $endTime - $startTime;
echo "Complex Switch Time: " . $complexSwitchTime . " seconds\n";

// Benchmark Complex Match
$startTime = microtime(true);
benchmarkComplexMatch($iterations);
$endTime = microtime(true);
$complexMatchTime = $endTime - $startTime;
echo "Complex Match Time: " . $complexMatchTime . " seconds\n";

$performanceImprovement = (($complexSwitchTime - $complexMatchTime) / $complexSwitchTime) * 100;
echo "Performance Improvement: " . $performanceImprovement . "%\n";
