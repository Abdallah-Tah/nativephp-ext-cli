<?php

namespace Amohamed\NativePhpCustomPhp\Lockfile;

use Amohamed\NativePhpCustomPhp\Build\BuildSpec;

final class LockfileManager
{
    public function read(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            return [];
        }

        /** @var mixed $decoded */
        $decoded = json_decode($contents, true);

        if (!is_array($decoded)) {
            return [];
        }

        return $decoded;
    }

    public function write(string $path, BuildSpec $spec): void
    {
        $payload = [
            'schema' => 1,
            'php' => $spec->phpVersion,
            'platform' => $spec->platform,
            'architecture' => $spec->architecture,
            'profile' => $spec->profile,
            'drivers' => $spec->drivers,
            'extensions' => $spec->extensions,
            'build_hash' => $spec->buildHash(),
        ];

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
