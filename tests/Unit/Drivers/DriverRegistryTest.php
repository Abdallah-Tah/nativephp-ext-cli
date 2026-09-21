<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit\Drivers;

use Amohamed\NativePhpCustomPhp\Drivers\DriverRegistry;
use PHPUnit\Framework\TestCase;

class DriverRegistryTest extends TestCase
{
    public function test_normalizes_aliases(): void
    {
        $registry = new DriverRegistry();

        $this->assertSame('postgres', $registry->normalize('pgsql'));
        $this->assertSame('sqlserver', $registry->normalize('mssql'));
    }

    public function test_applies_php_platform_compatibility_rules(): void
    {
        $registry = new DriverRegistry();

        $this->assertSame([], $registry->extensionsFor('sqlserver', '8.5', 'win'));
        $this->assertSame([], $registry->extensionsFor('sqlserver', '8.3', 'linux'));
        $this->assertSame(['sqlsrv', 'pdo_sqlsrv'], $registry->extensionsFor('sqlserver', '8.3', 'win'));
    }
}
