<?php

namespace Amohamed\NativePhpCustomPhp\Php;

final class SupportedPhpVersions
{
    /**
     * @var array<string, string>
     */
    private const CURRENT_PATCHES = [
        '8.1' => '8.1.31',
        '8.2' => '8.2.29',
        '8.3' => '8.3.15',
        '8.4' => '8.4.13',
        '8.5' => '8.5.3',
    ];

    /**
     * @return array<int, string>
     */
    public static function minors(): array
    {
        return array_keys(self::CURRENT_PATCHES);
    }

    /**
     * @return array<string, string>
     */
    public static function currentPatches(): array
    {
        return self::CURRENT_PATCHES;
    }

    public static function latestPatchForMinor(string $minor): ?string
    {
        return self::CURRENT_PATCHES[$minor] ?? null;
    }

    public static function isSupportedMinor(string $minor): bool
    {
        return isset(self::CURRENT_PATCHES[$minor]);
    }

    public static function isSupportedVersion(string $version): bool
    {
        if (preg_match('/^(\d+\.\d+)(?:\.\d+)?$/', $version, $matches) !== 1) {
            return false;
        }

        return self::isSupportedMinor($matches[1]);
    }
}
