<?php

return [

    /*
    |-----------------------------------------------------------------
    | Pre-Commit Hooks
    |-----------------------------------------------------------------
    |
    | The pre-commit hook is run first, before you even type in a commit message. It's used to inspect the snapshot
    | that's about to be committed, to see if you've forgotten something, to make sure tests run, or to examine
    | whatever you need to inspect in the code. Exiting non-zero from this hook aborts the commit, although you
    | can bypass it with git commit --no-verify. You can do things like check for code style (run lint or something
    | equivalent), check for trailing whitespace (the default hook does exactly this), or check for appropriate
    | documentation on new methods.
    |
    | These hooks must implement \Igorsgm\GitHooks\Contracts\PreCommitHook
    |
    */
    'pre-commit' => [

    ],

    /*
    |-----------------------------------------------------------------
    | Pre-Commit-Message Hooks
    |-----------------------------------------------------------------
    |
    | The prepare-commit-msg hook is run before the commit message editor is fired up but after the default message
    | is created. It lets you edit the default message before the commit author sees it. This hook takes a few
    | parameters: the path to the file that holds the commit message so far, the type of commit, and the commit
    | SHA-1 if this is an amended commit. This hook generally isn't useful for normal commits; rather, it's good for
    | commits where the default message is auto-generated, such as templated commit messages, merge commits, squashed
    | commits, and amended commits. You may use it in conjunction with a commit template to programmatically insert
    | information.
    |
    | These hooks must implement \Igorsgm\GitHooks\Contracts\MessageHook
    |
    */
    'prepare-commit-msg' => [

    ],

    /*
    |-----------------------------------------------------------------
    | Commit-Msg Hooks
    |-----------------------------------------------------------------
    |
    | The commit-msg hook takes one parameter, which again is the path to a temporary file that contains the commit
    | message written by the developer. If this script exits non-zero, Git aborts the commit process, so you can use
    | it to validate your project state or commit message before allowing a commit to go through.
    |
    | These hooks must implement \Igorsgm\GitHooks\Contracts\MessageHook
    |
    */
    'commit-msg' => [

    ],

    /*
    |-----------------------------------------------------------------
    | Post-Commit Hooks
    |-----------------------------------------------------------------
    |
    | After the entire commit process is completed, the post-commit hook runs. It doesn't take any parameters,
    | but you can easily get the last commit by running git log -1 HEAD. Generally, this script is used for
    | notification or something similar.
    |
    | These hooks must implement \Igorsgm\GitHooks\Contracts\PostCommitHook
    |
    */
    'post-commit' => [

    ],

    /*
    |-----------------------------------------------------------------
    | Pre-Rebase Hooks
    |-----------------------------------------------------------------
    |
    | @TODO Implement support to pre-rebase hooks
    |
    | The pre-rebase hook runs before you rebase anything and can halt the process by exiting non-zero.
    | You can use this hook to disallow rebasing any commits that have already been pushed.
    | The example pre-rebase hook that Git installs does this, although it makes some assumptions that may not
    | match with your workflow.
    |
    */
    'pre-rebase' => [

    ],

    /*
    |-----------------------------------------------------------------
    | Post-Rewrite Hooks
    |-----------------------------------------------------------------
    |
    | @TODO Implement support to post-rewrite hook
    |
    | The post-rewrite hook is run by commands that replace commits, such as git commit --amend and git rebase
    | (though not by git filter-branch). Its single argument is which command triggered the rewrite, and it receives
    | a list of rewrites on stdin. This hook has many of the same uses as the post-checkout and post-merge hooks.
    |
    */
    'post-rewrite' => [

    ],

    /*
    |-----------------------------------------------------------------
    | Post-Checkout Hooks
    |-----------------------------------------------------------------
    |
    | @TODO Implement support to post-checkout hook
    |
    | After you run a successful git checkout, the post-checkout hook runs; you can use it to set up your working
    | directory properly for your project environment. This may mean moving in large binary files that you don't want
    | source controlled, auto-generating documentation, or something along those lines.
    |
    */
    'post-checkout' => [

    ],

    /*
    |-----------------------------------------------------------------
    | Post-Merge Hooks
    |-----------------------------------------------------------------
    |
    | @TODO Implement support to post-merge hook
    |
    | The post-merge hook runs after a successful merge command. You can use it to restore data in the working tree
    | that Git can't track, such as permissions data. This hook can likewise validate the presence of files external
    | to Git control that you may want copied in when the working tree changes.
    |
    |
    */
    'post-merge' => [

    ],

    /*
    |-----------------------------------------------------------------
    | Pre-Push Hooks
    |-----------------------------------------------------------------
    |
    | The pre-push hook runs during git push, after the remote refs have been updated but before any objects have
    | been transferred. It receives the name and location of the remote as parameters, and a list of to-be-updated
    | refs through stdin. You can use it to validate a set of ref updates before a push occurs (a non-zero exit code
    | will abort the push).
    |
    | These hooks must implement \Igorsgm\GitHooks\Contracts\PrePushHook
    |
    */
    'pre-push' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Code Analyzers Configuration
    |--------------------------------------------------------------------------
    |
    | The following variables are used to store the paths to bin executables for
    | various code analyzer dependencies used in your Laravel application.
    | This configuration node allows you to set up the paths for these executables.
    | Here you can also specify the path to the configuration files they use to customize their behavior
    |
    */
    'code_analyzers' => [
        'laravel_pint' => [
            'path' => env('LARAVEL_PINT_PATH', 'vendor/bin/pint'),
            'config' => env('LARAVEL_PINT_CONFIG', 'pint.json'),
            'preset' => env('LARAVEL_PINT_PRESET', 'psr12'),
            'file_extensions' => env('LARAVEL_PINT_FILE_EXTENSIONS', '/\.php$/'),
        ],
        'php_code_sniffer' => [
            'phpcs_path' => env('PHPCS_PATH', 'vendor/bin/phpcs'),
            'phpcbf_path' => env('PHPCBF_PATH', 'vendor/bin/phpcbf'),
            'config' => env('PHPCS_STANDARD_CONFIG', 'phpcs.xml'),
            'file_extensions' => env('PHPCS_FILE_EXTENSIONS', '/\.php$/'),
        ],
        'larastan' => [
            'path' => env('LARASTAN_PATH', 'vendor/bin/phpstan'),
            'config' => env('LARASTAN_CONFIG', 'phpstan.neon'),
            'additional_params' => env('LARASTAN_ADDITIONAL_PARAMS', '--xdebug'),
        ],
        'blade_formatter' => [
            'path' => env('BLADE_FORMATTER_PATH', 'node_modules/.bin/blade-formatter'),
            'config' => env('BLADE_FORMATTER_CONFIG', '.bladeformatterrc.json'),
            'file_extensions' => env('BLADE_FORMATTER_FILE_EXTENSIONS', '/\.blade\.php$/'),
        ],
        'prettier' => [
            'path' => env('PRETTIER_PATH', 'node_modules/.bin/prettier'),
            'config' => env('PRETTIER_CONFIG', '.prettierrc.json'),
            'additional_params' => env('PRETTIER_ADDITIONAL_PARAMS', ''),
            'file_extensions' => env('PRETTIER_FILE_EXTENSIONS', '/\.(jsx?|tsx?|vue)$/'),
        ],
        'eslint' => [
            'path' => env('ESLINT_PATH', 'node_modules/.bin/eslint'),
            'config' => env('ESLINT_CONFIG', '.eslintrc.js'),
            'additional_params' => env('ESLINT_ADDITIONAL_PARAMS', ''),
            'file_extensions' => env('ESLINT_FILE_EXTENSIONS', '/\.(jsx?|tsx?|vue)$/'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Artisan Executable Path
    |--------------------------------------------------------------------------
    |
    | This configuration option allows you to set the path of the Artisan
    | executable within your Laravel or Laravel Zero application.
    |
    | By default, the path is set to the base_path('artisan') which corresponds
    | to the main Artisan executable in a standard Laravel installation.
    |
    | In case you are using a Laravel Zero CLI tool, you may need to change
    | this value to match the path of your main executable file instead
    | of artisan.php.
    |
    */
    'artisan_path' => base_path('artisan'),
];
