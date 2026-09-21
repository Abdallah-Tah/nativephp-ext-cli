<?php

namespace Amohamed\NativePhpCustomPhp\Platform;

final class PlatformDetector
{
    public function detect(?string $targetPlatform = null, ?string $targetArchitecture = null): PlatformInfo
    {
        $family = PHP_OS_FAMILY;
        $machine = php_uname('m');

        $platform = $targetPlatform !== null && $targetPlatform !== ''
            ? $this->normalizePlatform($targetPlatform)
            : $this->normalizePlatform($family);

        $architecture = $targetArchitecture !== null && $targetArchitecture !== ''
            ? $this->normalizeArchitecture($targetArchitecture)
            : $this->normalizeArchitecture($machine);

        return new PlatformInfo($platform, $architecture, $family, $machine);
    }

    public function normalizePlatform(string $value): string
    {
        return match (strtolower(trim($value))) {
            'win', 'windows' => 'win',
            'mac', 'macos', 'darwin', 'osx' => 'mac',
            'linux' => 'linux',
            default => 'linux',
        };
    }

    public function normalizeArchitecture(string $value): string
    {
        $normalized = strtolower(trim($value));

        return match ($normalized) {
            'x86_64', 'amd64', 'x64' => 'x64',
            'arm64', 'aarch64' => 'arm64',
            'x86', 'i386', 'i686' => 'x86',
            default => $normalized,
        };
    }
}
