<?php

namespace Amohamed\NativePhpCustomPhp\Drivers;

final class DriverRegistry
{
    /**
     * @var array<string, array<int, string>>
     */
    private array $driverExtensions = [
        'sqlite' => ['sqlite3', 'pdo_sqlite'],
        'mysql' => ['mysqli', 'pdo_mysql'],
        'postgres' => ['pgsql', 'pdo_pgsql'],
        'sqlserver' => ['sqlsrv', 'pdo_sqlsrv'],
    ];

    public function normalize(string $driver): ?string
    {
        return match (strtolower(trim($driver))) {
            'sqlite', 'sqlite3', 'pdo_sqlite' => 'sqlite',
            'mysql', 'mysqli', 'pdo_mysql' => 'mysql',
            'postgres', 'postgresql', 'pgsql', 'pdo_pgsql' => 'postgres',
            'sqlserver', 'sqlsrv', 'mssql', 'pdo_sqlsrv' => 'sqlserver',
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    public function normalizeMany(array $drivers): array
    {
        $normalized = [];

        foreach ($drivers as $driver) {
            if (!is_string($driver)) {
                continue;
            }

            $name = $this->normalize($driver);

            if ($name !== null && !in_array($name, $normalized, true)) {
                $normalized[] = $name;
            }
        }

        return $normalized;
    }

    /**
     * @return array<int, string>
     */
    public function extensionsFor(string $driver, string $phpVersion, string $platform): array
    {
        $normalized = $this->normalize($driver);

        if ($normalized === null) {
            return [];
        }

        if ($normalized === 'sqlserver' && version_compare($phpVersion, '8.4', '>=')) {
            return [];
        }

        if ($normalized === 'sqlserver' && $platform !== 'win') {
            return [];
        }

        return $this->driverExtensions[$normalized] ?? [];
    }

    /**
     * @return array<int, string>
     */
    public function extensionsForMany(array $drivers, string $phpVersion, string $platform): array
    {
        $extensions = [];

        foreach ($this->normalizeMany($drivers) as $driver) {
            $extensions = array_merge($extensions, $this->extensionsFor($driver, $phpVersion, $platform));
        }

        $extensions = array_values(array_unique($extensions));
        sort($extensions);

        return $extensions;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function all(): array
    {
        return $this->driverExtensions;
    }
}
