<?php

declare(strict_types=1);

// Fake binary that always fails — simulates a tool that finds issues and has no fix mode.
fwrite(STDOUT, 'fake error output'.PHP_EOL);
exit(1);
