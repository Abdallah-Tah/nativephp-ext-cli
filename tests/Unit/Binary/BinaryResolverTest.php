<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit\Binary;

use Amohamed\NativePhpCustomPhp\Binary\BinaryResolver;
use Amohamed\NativePhpCustomPhp\Build\BuildSpec;
use Amohamed\NativePhpCustomPhp\Cache\BinaryCache;
use Amohamed\NativePhpCustomPhp\Registry\ManifestClient;
use PHPUnit\Framework\TestCase;

class BinaryResolverTest extends TestCase
{
    public function test_returns_local_build_required_without_registry(): void
    {
        $cachePath = sys_get_temp_dir() . '/nativephp-ext-resolver-cache';
        $resolver = new BinaryResolver(new BinaryCache($cachePath), new ManifestClient(), null);

        $result = $resolver->resolve(BuildSpec::fromArray([
            'php' => '8.5.3',
            'platform' => 'linux',
            'architecture' => 'x64',
            'profile' => 'nativephp',
            'drivers' => [],
            'extensions' => ['pdo_sqlite'],
        ]));

        $this->assertSame('local-build-required', $result->type);
    }
}
