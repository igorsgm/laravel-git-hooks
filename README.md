<h1 align="center">🪝 Laravel Git Hooks</h1>

<p align="center">A powerful and easy-to-use package for managing Git hooks within your Laravel projects. Improve your code quality, reduce the time spent on code reviews, and catch potential bugs before they make it into your repository.</p>

<p align="center">
    <a href="https://packagist.org/packages/igorsgm/laravel-git-hooks">
        <img src="https://img.shields.io/packagist/v/igorsgm/laravel-git-hooks.svg?style=flat-square" alt="Latest Version on Packagist">
    </a>
    <a href="https://github.com/igorsgm/laravel-git-hooks/actions/workflows/main.yml/badge.svg">
        <img src="https://img.shields.io/github/actions/workflow/status/igorsgm/laravel-git-hooks/main.yml?style=flat-square" alt="Build Status">
    </a>
    <a href="https://packagist.org/packages/igorsgm/laravel-git-hooks">
        <img src="https://img.shields.io/packagist/dt/igorsgm/laravel-git-hooks.svg?style=flat-square" alt="Total Downloads">
    </a>
</p>

<hr/>

<p align="center">
    <img src="https://user-images.githubusercontent.com/14129843/234191523-859b962d-bfdf-4df7-88da-9c80ddb93607.png" alt="Laravel Git Hooks usage sample">
</p>

## ✨ Features

- **Pre-configured Hooks:** Laravel Git Hooks comes with pre-configured pre-commit hooks for popular tools, such as Laravel Pint, PHPCS, ESLint, Prettier, Larastan, Enlightn, and Blade Formatter, making it easy to enforce coding standards and style guidelines right away.
- **Manage Git Hooks:** Easily manage your Git hooks in your Laravel projects with a streamlined and organized approach.
- **Edit Commit Messages:** Gain control over your commit messages by customizing them to meet your project requirements and maintain a clean Git history.
- **Create Custom Hooks:** Add and integrate custom hooks tailored to your specific project needs, ensuring better code quality and adherence to guidelines.
- **Artisan Command for Hook Generation:** The package includes a convenient Artisan command that allows you to effortlessly generate new hooks of various types. Such as: `pre-commit`, `prepare-commit-msg`, `commit-msg`, `post-commit`, `pre-push`
- **Code Quality:** The package is thoroughly tested, with 100% of code coverage, ensuring its reliability and stability in a wide range of Laravel projects.

## 1️⃣ Installation

- You can install the package via composer:
```bash
composer require igorsgm/laravel-git-hooks --dev
```

- Publish the config file and customize it in the way you want:
```bash
php artisan vendor:publish --tag=laravel-git-hooks
```

- Now whenever you make a change in your `config/git-hooks.php` file, please register your git hooks by running the artisan command:
```bash
php artisan git-hooks:register
```

Once you've configured and registered the hooks, you're all set!

## 2️⃣ General Usage
### Usage of the configured pre-commit hooks
To use the already created pre-commit hooks of this package, you can simply edit the `pre-commit` section of git-hooks.php config file. Here's an example of how to configure them:
```php
'pre-commit' => [
    \Igorsgm\GitHooks\Console\Commands\Hooks\PintPreCommitHook::class, // Laravel Pint
    \Igorsgm\GitHooks\Console\Commands\Hooks\PHPCodeSnifferPreCommitHook::class, // PHPCS (with PHPCBF autofixer) 
    \Igorsgm\GitHooks\Console\Commands\Hooks\LarastanPreCommitHook::class, // Larastan
    \Igorsgm\GitHooks\Console\Commands\Hooks\EnlightnPreCommitHook::class, // Enlightn
    \Igorsgm\GitHooks\Console\Commands\Hooks\ESLintPreCommitHook::class, // ESLint
    \Igorsgm\GitHooks\Console\Commands\Hooks\PrettierPreCommitHook::class, // Prettier
],
```

### Creating Custom Git Hooks
1) If you need to create a custom Git hook for your project, Laravel Git Hooks makes it easy with the `git-hooks:make` Artisan command. To create a new custom hook, simply run the following command:
    ```bash
    php artisan git-hooks:make
    ```
    This command will prompt you to choose the type of hook you want to create (e.g., `pre-commit`, `post-commit`, etc.) and to provide a name for the hook. Once you've provided the required information, the command will generate a new hook class in the `app/Console/GitHooks` directory.
2) To start using your custom hook, open the generated file and implement the `handle()` method with your desired logic.
3) Add your custom hook to the appropriate array in the git-hooks.php config file:
```php
'pre-commit' => [
    // Other pre-commit hooks...
    \App\Console\GitHooks\MyCustomPreCommitHook::class,
],
```
4) Finally register your custom hook by running the artisan command:
```bash
php artisan git-hooks:register
```

## 3️⃣ Handling Git Hooks
### Pre-commit Hook
> The pre-commit hook is run first, before you even type in a commit message. It's used to inspect the snapshot that's
> about to be committed, to see if you've forgotten something, to make sure tests run, or to examine whatever you need to
> inspect in the code. Exiting non-zero from this hook aborts the commit, although you can bypass it with git commit
> --no-verify. You can do things like check for code style (run lint or something equivalent), check for trailing
> whitespace (the default hook does exactly this), or check for appropriate documentation on new methods.

```php
// config/git-hooks.php
return [
    ...
    'pre-commit' => [
        \App\Console\GitHooks\MyPreCommitHook::class,
    ],
    ...
];
```

```php
// App/Console/GitHooks/MyPreCommitHook.php

namespace App\Console\GitHooks;

use Closure;
use Igorsgm\GitHooks\Git\ChangedFiles;

class MyPreCommitHook implements \Igorsgm\GitHooks\Contracts\PreCommitHook
{
    // ...

    public function handle(ChangedFiles $files, Closure $next)
    {
        // TODO: Implement your pre commit hook logic here.

        // If you want to cancel the commit, you have to throw an exception.
        // i.e: throw new HookFailException();

        // Run the next hook in the chain
        return $next($files);
    }
}
```

### Prepare-commit-message Hook
> The prepare-commit-msg hook is run before the commit message editor is fired up but after the default message is
> created. It lets you edit the default message before the commit author sees it. This hook takes a few parameters: the
> path to the file that holds the commit message so far, the type of commit, and the commit SHA-1 if this is an amended
> commit. This hook generally isn't useful for normal commits; rather, it's good for commits where the default message is
> auto-generated, such as templated commit messages, merge commits, squashed commits, and amended commits. You may use it
> in conjunction with a commit template to programmatically insert information.

```php
// config/git-hooks.php
return [
    ...
    'prepare-commit-msg' => [
        \App\Console\GitHooks\MyPrepareCommitMessageHook::class,
    ],
    ...
];
```

```php
// App/Console/GitHooks/MyPrepareCommitMessageHook.php

namespace App\Console\GitHooks;

use Closure;
use Igorsgm\GitHooks\Git\CommitMessage;
use Igorsgm\GitHooks\Contracts\MessageHook;

class MyPrepareCommitMessageHook implements \Igorsgm\GitHooks\Contracts\MessageHook
{
    // ...

    public function handle(CommitMessage $message, Closure $next)
    {
        // TODO: Implement your prepare commit msg hook logic here.

        $currentMessage = $message->getMessage();
        // You can update commit message text
        $message->setMessage(str_replace('issue', 'fixed', $currentMessage));

        // If you want to cancel the commit, you have to throw an exception.
        // i.e: throw new HookFailException();

        // Run the next hook in the chain
        return $next($message);
    }
}
```

### Commit-msg Hook
> The commit-msg hook takes one parameter, which again is the path to a temporary file that contains the commit message
> written by the developer. If this script exits non-zero, Git aborts the commit process, so you can use


```php
// config/git-hooks.php
return [
    ...
    'commit-msg' => [
        \App\Console\GitHooks\MyCommitMessageHook::class,
    ],
    ...
];
```

The class structure of the `commit-msg` hook is the same as the `prepare-commit-msg` hook, shown right above.

### Post-commit Hook
> After the entire commit process is completed, the post-commit hook runs. It doesn't take any parameters, but you can
> easily get the last commit by running git log -1 HEAD. Generally, this script is used for notification or something
> similar.

```php
// config/git-hooks.php
return [
    ...
    'post-commit' => [
        \App\Console\GitHooks\MyPostCommitHook::class,
    ],
    ...
];
```

```php
// App/Console/GitHooks/MyPrepareCommitMessageHook.php

namespace App\Console\GitHooks;

use Closure;
use Igorsgm\GitHooks\Git\CommitMessage;
use Igorsgm\GitHooks\Contracts\PostCommitHook;

class MyPostCommitHook implements \Igorsgm\GitHooks\Contracts\PostCommitHook
{
    // ...

    public function handle(Log $log, Closure $next)
    {
        // TODO: Implement post commit hook logic here.

        // You can interact with the commit log
        $hash = $log->getHash();
        $author = $log->getAuthor();
        $date = $log->getDate();
        $message = $log->getMessage();

        // If you want to cancel the commit, you have to throw an exception.
        // i.e: throw new HookFailException();

        // Run the next hook in the chain
        return $next($log);
    }
}
```

### Pre-push Hook
> The pre-push hook runs during git push, after the remote refs have been updated but before any objects have been
transferred. It receives the name and location of the remote as parameters, and a list of to-be-updated refs through
stdin. You can use it to validate a set of ref updates before a push occurs (a non-zero exit code will abort the push).

```php
// config/git-hooks.php
return [
    ...
    'pre-push' => [
        \App\Console\GitHooks\MyPrePushHook::class,
    ],
    ...
];
```

The class structure of the `pre-push` hooks is the same as the `post-commit` hook shown right above, but implementing `\Igorsgm\GitHooks\Contracts\PrePushHook` interface.

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Igor Moraes](https://github.com/igorsgm)
- [Pavel Buchnev](https://github.com/butschster)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
