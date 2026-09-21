<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit\Registry;

use Amohamed\NativePhpCustomPhp\Registry\BinaryManifest;
use PHPUnit\Framework\TestCase;

class BinaryManifestTest extends TestCase
{
    public function test_parses_manifest_schema(): void
    {
        $manifest = BinaryManifest::fromJson((string) json_encode([
            'schema' => 1,
            'generated_at' => '2026-09-21T12:00:00Z',
            'artifacts' => [[
                'php' => '8.5.3',
                'php_minor' => '8.5',
                'platform' => 'win-x64',
                'profile' => 'mysql',
                'extensions' => ['mysqli', 'pdo_mysql'],
                'url' => 'https://example.com/php.zip',
                'sha256' => 'abc',
                'size' => 123,
            ]],
        ]));

        $this->assertCount(1, $manifest->artifacts);
        $this->assertSame('8.5.3', $manifest->artifacts[0]->php);
    }
}
