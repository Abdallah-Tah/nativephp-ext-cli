<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit\Php;

use Amohamed\NativePhpCustomPhp\Php\PhpReleaseLookup;
use Amohamed\NativePhpCustomPhp\Php\PhpVersionResolver;
use PHPUnit\Framework\TestCase;

class PhpVersionResolverTest extends TestCase
{
    public function test_resolves_exact_patch_versions(): void
    {
        $resolver = new PhpVersionResolver();
        $resolved = $resolver->resolve('8.5.3');

        $this->assertSame('8.5', $resolved->minor);
        $this->assertSame('8.5.3', $resolved->resolved);
        $this->assertTrue($resolved->exactRequested);
    }

    public function test_resolves_minor_with_lookup_fallback(): void
    {
        $lookup = new class() implements PhpReleaseLookup {
            public function latestReleaseForMinor(string $minor): ?array
            {
                return ['version' => '8.5.4', 'sources' => []];
            }
        };

        $resolver = new PhpVersionResolver($lookup);
        $resolved = $resolver->resolve('8.5');

        $this->assertSame('8.5.4', $resolved->resolved);
        $this->assertTrue($resolved->fromNetwork);
    }

    public function test_rejects_unsupported_minor(): void
    {
        $this->expectException(\RuntimeException::class);

        (new PhpVersionResolver())->resolve('8.0');
    }
}
