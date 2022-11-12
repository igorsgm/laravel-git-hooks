<?php

dataset('lastCommitLogText', [
    'default git log' => sprintf('commit %s
Author: Igor Moraes <igor.sgm@gmail.com>
Date:   Wed Nov 9 04:50:40 2022 -0800

    wip
', mockCommitHash()),
]);

dataset('mergeLogText', [
    'merge git log' => sprintf("commit %s
Merge: 123abc 456def
Author: Igor Moraes <igor.sgm@gmail.com>
Date:   Wed Nov 9 04:50:40 2022 -0800

    Merge branch 'main' of github.com:igorsgm/laravel-git-hooks

", mockCommitHash()),
]);

dataset('listOfChangedFiles', [
    'list of changed files' => 'AM src/ChangedFiles.php',
]);
