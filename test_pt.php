<?php
require_once 'src/DeadlineCalculator.php';

// Test case: 2026-02-02 (Monday) + 5 days -> 2026-02-09 (Monday)
$result = DeadlineCalculator::calculate('2026-02-02', 5, 'working');
echo "Output: " . $result['description'] . "\n";

// Test Friday
$result2 = DeadlineCalculator::calculate('2026-02-13', 1, 'calendar'); 
// 13(Fri) + 1 = 14(Sat) -> Pushes to Mon 16? Or similar.
echo "Output 2: " . $result2['description'] . "\n";
