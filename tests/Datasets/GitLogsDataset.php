<?php

dataset('lastCommitLogText', [
    'Default Git Log' => sprintf('commit %s
Author: Igor Moraes <igor.sgm@gmail.com>
Date:   Wed Nov 9 04:50:40 2022 -0800

    wip
', mockCommitHash()),
]);

dataset('mergeLogText', [
    'Merge Git Log' => sprintf("commit %s
Merge: 123abc 456def
Author: Igor Moraes <igor.sgm@gmail.com>
Date:   Wed Nov 9 04:50:40 2022 -0800

    Merge branch 'main' of github.com:igorsgm/laravel-git-hooks

", mockCommitHash()),
]);

dataset('listOfChangedFiles', [
    'List Of Changed Files' => 'AM src/ChangedFiles.php',
]);

dataset('listOfFixablePhpFiles', [
    'List of Fixable Files' => implode(PHP_EOL, [
        'AM temp/ClassWithFixableIssues.php',
        'AM temp/fixable-blade-file.blade.php',
    ]),
]);

dataset('listOfFixableJSFiles', [
    'List of Fixable Files' => implode(PHP_EOL, [
        'AM temp/fixable-js-file.js',
    ]),
]);

dataset('listOfNonFixableJSFiles', [
    'List of Fixable Files' => implode(PHP_EOL, [
        'AM temp/not-fully-fixable-js-file.js',
    ]),
]);
