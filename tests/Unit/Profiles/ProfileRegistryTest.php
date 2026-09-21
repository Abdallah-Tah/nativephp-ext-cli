<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit\Profiles;

use Amohamed\NativePhpCustomPhp\Profiles\ProfileRegistry;
use PHPUnit\Framework\TestCase;

class ProfileRegistryTest extends TestCase
{
    public function test_nativephp_profile_includes_sodium(): void
    {
        $profile = (new ProfileRegistry())->resolve('nativephp', '8.5', 'linux');

        $this->assertContains('sodium', $profile->extensions);
        $this->assertContains('pdo_sqlite', $profile->extensions);
        $this->assertContains('sqlite3', $profile->extensions);
        $this->assertContains('mbstring', $profile->extensions);
        $this->assertContains('zip', $profile->extensions);
    }

    public function test_databases_profile_collects_supported_drivers(): void
    {
        $profile = (new ProfileRegistry())->resolve('databases', '8.3', 'win');

        $this->assertContains('mysqli', $profile->extensions);
        $this->assertContains('pgsql', $profile->extensions);
        $this->assertContains('sqlsrv', $profile->extensions);
    }
}
