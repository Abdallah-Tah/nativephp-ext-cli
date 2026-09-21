<?php

namespace Amohamed\NativePhpCustomPhp\Profiles;

use Amohamed\NativePhpCustomPhp\Drivers\DriverRegistry;

final class ProfileRegistry
{
    public function __construct(
        private readonly NativePhpExtensionProvider $nativePhpExtensionProvider = new NativePhpExtensionProvider(),
        private readonly DriverRegistry $driverRegistry = new DriverRegistry(),
    ) {
    }

    /**
     * @param array<int, string> $customExtensions
     */
    public function resolve(string $profile, string $phpVersion, string $platform, array $customExtensions = []): Profile
    {
        $name = strtolower(trim($profile));
        $base = $this->nativePhpExtensionProvider->baseExtensions();

        return match ($name) {
            'nativephp' => new Profile('nativephp', [], $this->normalize($base)),
            'mysql' => $this->withDrivers('mysql', ['mysql'], $base, $phpVersion, $platform),
            'postgres' => $this->withDrivers('postgres', ['postgres'], $base, $phpVersion, $platform),
            'sqlserver' => $this->withDrivers('sqlserver', ['sqlserver'], $base, $phpVersion, $platform),
            'mysql-postgres' => $this->withDrivers('mysql-postgres', ['mysql', 'postgres'], $base, $phpVersion, $platform),
            'databases' => $this->withDrivers('databases', ['mysql', 'postgres', 'sqlserver'], $base, $phpVersion, $platform),
            'custom' => new Profile('custom', [], $this->normalize(array_merge($base, $customExtensions))),
            default => new Profile('nativephp', [], $this->normalize($base)),
        };
    }

    /**
     * @return array<int, string>
     */
    public function names(): array
    {
        return ['nativephp', 'mysql', 'postgres', 'sqlserver', 'mysql-postgres', 'databases', 'custom'];
    }

    /**
     * @param array<int, string> $drivers
     * @param array<int, string> $base
     */
    private function withDrivers(string $name, array $drivers, array $base, string $phpVersion, string $platform): Profile
    {
        $extensions = array_merge($base, $this->driverRegistry->extensionsForMany($drivers, $phpVersion, $platform));

        return new Profile($name, $drivers, $this->normalize($extensions));
    }

    /**
     * @param array<int, string> $extensions
     * @return array<int, string>
     */
    private function normalize(array $extensions): array
    {
        $extensions = array_values(array_unique(array_map(static fn (string $ext): string => strtolower(trim($ext)), $extensions)));
        sort($extensions);

        return $extensions;
    }
}
