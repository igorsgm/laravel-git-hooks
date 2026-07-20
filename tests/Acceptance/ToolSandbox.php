<?php

declare(strict_types=1);

namespace Igorsgm\GitHooks\Tests\Acceptance;

use RuntimeException;
use stdClass;

/**
 * Installs a single tool in its own isolated temp directory.
 *
 * Each tool gets a dedicated subdirectory under the OS temp folder so that
 * packages never interfere with each other (different PHP/Node versions,
 * conflicting dependencies, etc.).  The directory is cached between runs —
 * first run is slow (downloads the package), subsequent runs reuse it.
 *
 * Usage in a Pest acceptance test:
 *
 *   $sandbox = ToolSandbox::php('phpcodesniffer', 'squizlabs/php_codesniffer', 'phpcs');
 *
 *   beforeEach(function () use ($sandbox) {
 *       try {
 *           $sandbox->install();
 *       } catch (Throwable $e) {
 *           $this->markTestSkipped($e->getMessage());
 *       }
 *       $this->gitInit();
 *       $this->initializeTempDirectory(base_path('temp'));
 *   });
 */
final class ToolSandbox
{
    private const CACHE_DIR_NAME = 'laravel-git-hooks-acceptance';

    private string $sandboxDir;

    private function __construct(
        private readonly string $toolName,
        private readonly string $package,
        private readonly string $binary,
        private readonly string $manager, // 'composer' | 'npm'
    ) {
        $this->sandboxDir = implode(DIRECTORY_SEPARATOR, [
            sys_get_temp_dir(),
            self::CACHE_DIR_NAME,
            $this->toolName,
        ]);
    }

    /** Install a PHP tool via Composer in its own isolated sandbox. */
    public static function php(string $toolName, string $composerPackage, string $binary): self
    {
        return new self($toolName, $composerPackage, $binary, 'composer');
    }

    /** Install a JS tool via npm in its own isolated sandbox. */
    public static function js(string $toolName, string $npmPackage, string $binary): self
    {
        return new self($toolName, $npmPackage, $binary, 'npm');
    }

    /** Remove all sandbox directories created by this class. */
    public static function cleanupAll(): void
    {
        $root = sys_get_temp_dir().DIRECTORY_SEPARATOR.self::CACHE_DIR_NAME;
        if (is_dir($root)) {
            self::deleteDirectory($root);
        }
    }

    /**
     * Ensure the tool is installed. No-op if the binary is already present
     * (cached from a previous run). Throws RuntimeException on failure.
     */
    public function install(): void
    {
        if ($this->isBinaryInstalled()) {
            return;
        }

        if (!is_dir($this->sandboxDir)) {
            mkdir($this->sandboxDir, 0755, true);
        }

        if ($this->manager === 'composer') {
            $this->installWithComposer();
        } else {
            $this->installWithNpm();
        }

        if (!$this->isBinaryInstalled()) {
            throw new RuntimeException(
                "Failed to install {$this->toolName} ({$this->package}). "
                .'Check your internet connection and that the package name is correct.'
            );
        }
    }

    /** Absolute path to the installed binary (no .bat suffix — the OS resolves that). */
    public function binaryPath(): string
    {
        if ($this->manager === 'composer') {
            return implode(DIRECTORY_SEPARATOR, [
                $this->sandboxDir, 'vendor', 'bin', $this->binary,
            ]);
        }

        return implode(DIRECTORY_SEPARATOR, [
            $this->sandboxDir, 'node_modules', '.bin', $this->binary,
        ]);
    }

    public function isBinaryInstalled(): bool
    {
        return file_exists($this->binaryPath())
            || file_exists($this->binaryPath().'.bat')
            || file_exists($this->binaryPath().'.cmd');
    }

    private static function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $items = array_diff((array) scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir.DIRECTORY_SEPARATOR.$item;
            if (is_dir($path) && !is_link($path)) {
                self::deleteDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    private function installWithComposer(): void
    {
        $composerJson = $this->sandboxDir.DIRECTORY_SEPARATOR.'composer.json';
        if (!file_exists($composerJson)) {
            file_put_contents($composerJson, json_encode(
                [
                    'require' => new stdClass,
                    'config' => [
                        'sort-packages' => true,
                        // Allow all Composer plugins so sandbox installs work without interaction.
                        'allow-plugins' => true,
                    ],
                ],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            ));
        }

        $cmd = sprintf(
            '%s require %s --working-dir=%s --no-interaction --no-progress --quiet 2>&1',
            'composer',
            escapeshellarg($this->package),
            escapeshellarg($this->sandboxDir)
        );

        shell_exec($cmd);
    }

    private function installWithNpm(): void
    {
        $packageJson = $this->sandboxDir.DIRECTORY_SEPARATOR.'package.json';
        if (!file_exists($packageJson)) {
            file_put_contents($packageJson, '{}');
        }

        $cmd = sprintf(
            'npm install %s --prefix %s --no-save --quiet 2>&1',
            escapeshellarg($this->package),
            escapeshellarg($this->sandboxDir)
        );

        shell_exec($cmd);
    }
}
