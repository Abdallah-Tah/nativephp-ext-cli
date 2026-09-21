<?php

namespace Amohamed\NativePhpCustomPhp\Php;

use RuntimeException;

final class PhpVersionResolver
{
    public function __construct(
        private readonly ?PhpReleaseLookup $lookup = null,
    ) {
    }

    public function resolve(string $version): ResolvedPhpVersion
    {
        $version = trim($version);

        if (!preg_match('/^(?<major>\d+)\.(?<minor>\d+)(?:\.(?<patch>\d+))?$/', $version, $matches)) {
            throw new RuntimeException('Invalid PHP version format. Use format: 8.5 or 8.5.3');
        }

        $minor = $matches['major'] . '.' . $matches['minor'];

        if (!SupportedPhpVersions::isSupportedMinor($minor)) {
            throw new RuntimeException("Unsupported PHP version '{$minor}'. Supported versions: " . implode(', ', SupportedPhpVersions::minors()));
        }

        $exactRequested = isset($matches['patch']) && $matches['patch'] !== '';

        if ($exactRequested) {
            if (!SupportedPhpVersions::isSupportedMinor($minor)) {
                throw new RuntimeException("Unsupported PHP minor '{$minor}'.");
            }

            $latest = SupportedPhpVersions::latestPatchForMinor($minor);
            if ($latest !== null && version_compare($version, $latest, '>')) {
                throw new RuntimeException("Unsupported PHP patch '{$version}'. Latest known patch for {$minor} is {$latest}.");
            }

            return new ResolvedPhpVersion($version, $minor, $version, true, false, []);
        }

        $lookup = $this->lookup;
        if ($lookup !== null) {
            $release = $lookup->latestReleaseForMinor($minor);
            if ($release !== null && isset($release['version']) && is_string($release['version'])) {
                return new ResolvedPhpVersion($version, $minor, $release['version'], false, true, $release['sources'] ?? []);
            }
        }

        $fallback = SupportedPhpVersions::latestPatchForMinor($minor);

        if ($fallback === null) {
            throw new RuntimeException("Unable to resolve PHP version '{$minor}'.");
        }

        return new ResolvedPhpVersion($version, $minor, $fallback, false, false, []);
    }
}
