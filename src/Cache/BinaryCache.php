<?php

namespace Amohamed\NativePhpCustomPhp\Cache;

use Amohamed\NativePhpCustomPhp\Build\BuildSpec;
use RuntimeException;

final class BinaryCache
{
    public function __construct(
        private readonly ?string $basePath = null,
    ) {
    }

    public function root(): string
    {
        $path = $this->basePath;

        if ($path === null || $path === '') {
            if (PHP_OS_FAMILY === 'Windows') {
                $localAppData = getenv('LOCALAPPDATA') ?: sys_get_temp_dir();
                $path = rtrim($localAppData, '\\/') . DIRECTORY_SEPARATOR . 'nativephp-ext';
            } else {
                $home = getenv('HOME') ?: sys_get_temp_dir();
                $path = rtrim($home, '\\/') . DIRECTORY_SEPARATOR . '.nativephp-ext';
            }
        }

        return $path;
    }

    public function ensureStructure(): void
    {
        foreach ([$this->root(), $this->binariesPath(), $this->manifestsPath(), $this->buildsPath()] as $path) {
            if (!is_dir($path) && !@mkdir($path, 0775, true) && !is_dir($path)) {
                throw new RuntimeException('Unable to create cache directory: ' . $path);
            }
        }
    }

    public function binariesPath(): string
    {
        return $this->root() . DIRECTORY_SEPARATOR . 'binaries';
    }

    public function manifestsPath(): string
    {
        return $this->root() . DIRECTORY_SEPARATOR . 'manifests';
    }

    public function buildsPath(): string
    {
        return $this->root() . DIRECTORY_SEPARATOR . 'builds';
    }

    public function cacheKey(BuildSpec $spec): string
    {
        return hash('sha256', implode('|', [
            $spec->phpVersion,
            $spec->platform,
            $spec->architecture,
            $spec->profile,
            implode(',', $spec->drivers),
            implode(',', $spec->extensions),
            implode(',', $spec->buildFlags),
        ]));
    }

    public function pathFor(BuildSpec $spec, string $extension = 'zip'): string
    {
        return $this->binariesPath() . DIRECTORY_SEPARATOR . $this->cacheKey($spec) . '.' . ltrim($extension, '.');
    }

    public function has(BuildSpec $spec): bool
    {
        return file_exists($this->pathFor($spec, 'zip'));
    }
}
