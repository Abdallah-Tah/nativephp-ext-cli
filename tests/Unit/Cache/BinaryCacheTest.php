<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit\Cache;

use Amohamed\NativePhpCustomPhp\Build\BuildSpec;
use Amohamed\NativePhpCustomPhp\Cache\BinaryCache;
use PHPUnit\Framework\TestCase;

class BinaryCacheTest extends TestCase
{
    public function test_cache_key_changes_when_build_dimensions_change(): void
    {
        $cache = new BinaryCache(sys_get_temp_dir() . '/nativephp-ext-test-cache');
        $spec = BuildSpec::fromArray([
            'php' => '8.5.3',
            'platform' => 'win',
            'architecture' => 'x64',
            'profile' => 'mysql',
            'drivers' => ['mysql'],
            'extensions' => ['mysqli', 'pdo_mysql'],
            'build_flags' => ['--debug'],
        ]);
        $specWithDifferentFlags = BuildSpec::fromArray([
            'php' => '8.5.3',
            'platform' => 'win',
            'architecture' => 'x64',
            'profile' => 'mysql',
            'drivers' => ['mysql'],
            'extensions' => ['mysqli', 'pdo_mysql'],
            'build_flags' => ['--release'],
        ]);

        $keyA = $cache->cacheKey($spec);
        $keyB = $cache->cacheKey($specWithDifferentFlags);

        $this->assertNotSame($keyA, $keyB);
    }
}
