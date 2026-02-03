<?php
// test_logic.php
require_once 'config.php';
require_once 'src/DeadlineCalculator.php';

// Test Case 1: Standard Week
echo "Test 1: Normal Week (Start Mon 02/02/2026 + 5 days)\n";
$res = DeadlineCalculator::calculate('2026-02-02', 5, 'working');
print_r($res);

// Test Case 2: Weekend span
echo "\nTest 2: Weekend Span (Start Fri 06/02/2026 + 5 days)\n";
// Pub: Fri 06. Term Start: Mon 09 (if no holiday).
// Count: Tue(1), Wed(2), Thu(3), Fri(4), Mon(5). End: Mon 16.
$res = DeadlineCalculator::calculate('2026-02-06', 5, 'working');
print_r($res);

// Test Case 3: Recess Forense (Start Dec 19 + 5 days)
echo "\nTest 3: Recess (Start 2024-12-19 + 5 days)\n";
// Pub: Dec 19. Term Start: Jan 21 (First day after recess).
// Actually: Recess is 20 Dec - 20 Jan.
// If Pub 19 Dec. Start (Day 1) would be 20 Dec -> Suspended.
// So Term Start waits until 21 Jan.
$res = DeadlineCalculator::calculate('2024-12-19', 5, 'working');
print_r($res);
