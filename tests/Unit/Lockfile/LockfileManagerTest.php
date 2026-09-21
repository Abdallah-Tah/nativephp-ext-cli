<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit\Lockfile;

use Amohamed\NativePhpCustomPhp\Build\BuildSpec;
use Amohamed\NativePhpCustomPhp\Lockfile\LockfileManager;
use PHPUnit\Framework\TestCase;

class LockfileManagerTest extends TestCase
{
    public function test_writes_and_reads_lockfile_schema(): void
    {
        $path = sys_get_temp_dir() . '/nativephp-ext-test.lock';
        @unlink($path);

        $spec = BuildSpec::fromArray([
            'php' => '8.5.3',
            'platform' => 'win',
            'architecture' => 'x64',
            'profile' => 'mysql',
            'drivers' => ['mysql'],
            'extensions' => ['mysqli', 'pdo_mysql'],
        ]);

        $manager = new LockfileManager();
        $manager->write($path, $spec);
        $loaded = $manager->read($path);

        $this->assertSame(1, $loaded['schema']);
        $this->assertSame('8.5.3', $loaded['php']);
        $this->assertSame('mysql', $loaded['profile']);
    }
}
