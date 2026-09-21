<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit\Binary;

use Amohamed\NativePhpCustomPhp\Binary\BinaryVerifier;
use PHPUnit\Framework\TestCase;

class BinaryVerifierTest extends TestCase
{
    public function test_rejects_missing_files(): void
    {
        $result = (new BinaryVerifier())->verifyArchive(sys_get_temp_dir() . '/missing-nativephp-ext.zip');

        $this->assertFalse($result->valid);
    }
}
