<?php

declare(strict_types=1);

// Fake binary that simulates a tool with both analyze and fix modes.
//
// Analyze mode keywords (exits 1 — simulates finding issues):
//   --test    (Pint)
//   --dry-run (Rector)
//   check     (PHPCSFixer)
//
// Fix mode (no matching keyword in argv → exits 0 — simulates successful fix).
$analyzeKeywords = ['--test', '--dry-run', 'check'];
$isAnalyzeMode = (bool) array_intersect(array_slice($argv, 1), $analyzeKeywords);
exit($isAnalyzeMode ? 1 : 0);
