<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit\Build;

use Amohamed\NativePhpCustomPhp\Build\BuildSpec;
use PHPUnit\Framework\TestCase;

class BuildSpecTest extends TestCase
{
    public function test_normalization_and_hash_are_deterministic(): void
    {
        $a = BuildSpec::fromArray([
            'php' => '8.5.3',
            'platform' => 'WIN',
            'architecture' => 'X64',
            'profile' => 'MYSQL',
            'drivers' => ['mysql', 'mysql'],
            'extensions' => ['pdo_mysql', 'mysqli'],
            'build_flags' => ['--debug'],
        ]);

        $b = BuildSpec::fromArray([
            'php' => '8.5.3',
            'platform' => 'win',
            'architecture' => 'x64',
            'profile' => 'mysql',
            'drivers' => ['mysql'],
            'extensions' => ['mysqli', 'pdo_mysql'],
            'build_flags' => ['--debug'],
        ]);

        $this->assertSame($a->buildHash(), $b->buildHash());
    }
}
