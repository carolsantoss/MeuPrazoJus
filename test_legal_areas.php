<?php
// test_legal_areas.php
require_once 'config.php';
require_once 'src/DeadlineCalculator.php';

function test($name, $startDate, $days, $type, $matter, $expectedEnd) {
    echo "Testing $name...\n";
    $res = DeadlineCalculator::calculate($startDate, $days, $type, null, null, $matter);
    $passed = ($res['end_date'] === $expectedEnd);
    echo $passed ? "✅ PASSED" : "❌ FAILED (Expected $expectedEnd, got {$res['end_date']})";
    echo "\n\n";
}

// 1. CIVIL (Working days, skips weekends and recess)
// Pub Thu 18/12/2025. 
// Day 1: Fri 19/12. 
// Day 2 (Suspended): Sat 20/12.
// ... Recess until 20/01 ...
// Day 2 (Resumes): Wed 21/01/2026.
// Day 3: Thu 22/01.
// Day 4: Fri 23/01.
// Day 5: Mon 26/01. (Sat 24, Sun 25 skipped)
test("Civil 5 Working Days (Recess cross)", '2025-12-18', 5, 'working', 'CIVIL', '2026-01-26');

// 2. CRIMINAL (Continuous days, skips suspension, but prorogates end if weekend)
// Pub Thu 18/12/2025.
// Day 1: Fri 19/12.
// Day 2: Sat 20/12. (Continuous!)
// Day 3: Sun 21/12.
// Day 4: Mon 22/12.
// Day 5: Tue 23/12.
test("Criminal 5 Continuous Days (Recess ignore)", '2025-12-18', 5, 'calendar', 'CRIMINAL', '2025-12-23');

// 3. CRIMINAL End Date on Weekend
// Pub Wed 04/02/2026.
// Day 1: Thu 05/02.
// Day 2: Fri 06/02.
// Day 3: Sat 07/02.
// Day 4: Sun 08/02.
// Day 5: Mon 09/02.
test("Criminal 3 Continuous Days (End on Sat -> Mon)", '2026-02-04', 3, 'calendar', 'CRIMINAL', '2026-02-09');

// 4. LABOR (Working days, similar to Civil)
test("Labor 8 Working Days", '2026-02-02', 8, 'working', 'LABOR', '2026-02-12');
