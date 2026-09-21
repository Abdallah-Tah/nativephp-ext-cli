<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit\Platform;

use Amohamed\NativePhpCustomPhp\Platform\PlatformDetector;
use PHPUnit\Framework\TestCase;

class PlatformDetectorTest extends TestCase
{
    public function test_normalizes_platform_values(): void
    {
        $detector = new PlatformDetector();

        $this->assertSame('win', $detector->normalizePlatform('Windows'));
        $this->assertSame('mac', $detector->normalizePlatform('Darwin'));
        $this->assertSame('linux', $detector->normalizePlatform('Linux'));
    }

    public function test_detect_supports_explicit_target_values(): void
    {
        $detector = new PlatformDetector();
        $info = $detector->detect('windows', 'amd64');

        $this->assertSame('win', $info->platform);
        $this->assertSame('x64', $info->architecture);
    }

    public function test_unknown_inputs_preserve_architecture_value(): void
    {
        $detector = new PlatformDetector();
        $info = $detector->detect('unknown-os', 'unknown-arch');

        $this->assertSame('linux', $info->platform);
        $this->assertSame('unknown-arch', $info->architecture);
    }
}
