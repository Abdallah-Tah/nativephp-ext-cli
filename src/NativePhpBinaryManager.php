<?php

namespace Amohamed\NativePhpCustomPhp;

use Amohamed\NativePhpCustomPhp\Binary\BinaryResolver;
use Amohamed\NativePhpCustomPhp\Build\BuildSpec;
use Amohamed\NativePhpCustomPhp\Drivers\DriverRegistry;
use Amohamed\NativePhpCustomPhp\Php\PhpVersionResolver;
use Amohamed\NativePhpCustomPhp\Php\SupportedPhpVersions;
use Amohamed\NativePhpCustomPhp\Platform\PlatformDetector;
use Amohamed\NativePhpCustomPhp\Profiles\ProfileRegistry;

final class NativePhpBinaryManager
{
    public function __construct(
        private readonly PhpVersionResolver $phpVersionResolver,
        private readonly PlatformDetector $platformDetector,
        private readonly DriverRegistry $driverRegistry,
        private readonly ProfileRegistry $profileRegistry,
        private readonly BinaryResolver $binaryResolver,
    ) {
    }

    public function availableProfiles(): array
    {
        return $this->profileRegistry->names();
    }

    public function availablePhpVersions(): array
    {
        return SupportedPhpVersions::currentPatches();
    }

    public function resolve(BuildSpec $spec): array
    {
        return $this->binaryResolver->resolve($spec)->toArray();
    }

    public function install(BuildSpec $spec): array
    {
        return $this->resolve($spec);
    }

    public function doctor(): array
    {
        $platform = $this->platformDetector->detect();

        return [
            'platform' => $platform->platform,
            'architecture' => $platform->architecture,
            'php_version' => PHP_VERSION,
            'supported_php_versions' => SupportedPhpVersions::currentPatches(),
            'drivers' => array_keys($this->driverRegistry->all()),
            'profiles' => $this->availableProfiles(),
        ];
    }

    public function buildSpec(string $phpVersion, string $profile, array $drivers = [], array $extensions = [], array $buildFlags = []): BuildSpec
    {
        $resolvedVersion = $this->phpVersionResolver->resolve($phpVersion);
        $platform = $this->platformDetector->detect();

        if ($profile !== 'custom') {
            $profileDefinition = $this->profileRegistry->resolve($profile, $resolvedVersion->minor, $platform->platform, $extensions);
            $drivers = $profileDefinition->drivers;
            $extensions = $profileDefinition->extensions;
            $profile = $profileDefinition->name;
        } else {
            $drivers = $this->driverRegistry->normalizeMany($drivers);
            $extensions = array_values(array_unique(array_merge(
                $extensions,
                $this->driverRegistry->extensionsForMany($drivers, $resolvedVersion->minor, $platform->platform)
            )));
            sort($extensions);
        }

        return new BuildSpec(
            phpVersion: $resolvedVersion->resolved,
            platform: $platform->platform,
            architecture: $platform->architecture,
            drivers: $drivers,
            extensions: $extensions,
            profile: $profile,
            buildFlags: $buildFlags,
        );
    }
}
