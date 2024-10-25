<?php

function benchmarkSwitch($iterations) {
    $result = 0;
    for ($i = 0; $i < $iterations; $i++) {
        $case = $i % 10;
        switch ($case) {
            case 0: $result += 1; break;
            case 1: $result += 2; break;
            case 2: $result += 3; break;
            case 3: $result += 4; break;
            case 4: $result += 5; break;
            case 5: $result += 6; break;
            case 6: $result += 7; break;
            case 7: $result += 8; break;
            case 8: $result += 9; break;
            case 9: $result += 10; break;
            default: $result += 0; break;
        }
    }
    return $result;
}

function benchmarkMatch($iterations) {
    $result = 0;
    for ($i = 0; $i < $iterations; $i++) {
        $case = $i % 10;
        $result += match ($case) {
            0 => 1,
            1 => 2,
            2 => 3,
            3 => 4,
            4 => 5,
            5 => 6,
            6 => 7,
            7 => 8,
            8 => 9,
            9 => 10,
            default => 0,
        };
    }
    return $result;
}

// Number of iterations for the benchmark
$iterations = 10000000;

// Benchmark Switch
$startTime = microtime(true);
benchmarkSwitch($iterations);
$endTime = microtime(true);
$switchTime = $endTime - $startTime;
echo "Switch Time: " . $switchTime . " seconds\n";

// Benchmark Match
$startTime = microtime(true);
benchmarkMatch($iterations);
$endTime = microtime(true);
$matchTime = $endTime - $startTime;
echo "Match Time: " . $matchTime . " seconds\n";

$performanceImprovement = (($switchTime - $matchTime) / $switchTime) * 100;
echo "Performance Improvement: " . $performanceImprovement . "%\n";
