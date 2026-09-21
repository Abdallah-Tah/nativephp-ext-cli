<?php

namespace Amohamed\NativePhpCustomPhp\Binary;

use ZipArchive;

final class BinaryVerifier
{
    /**
     * @param array<int, string> $expectedEntries
     */
    public function verifyArchive(string $path, ?string $expectedSha256 = null, array $expectedEntries = []): VerificationResult
    {
        $errors = [];

        if (!file_exists($path)) {
            $errors[] = 'File does not exist.';
            return new VerificationResult(false, $errors);
        }

        if ($expectedSha256 !== null && $expectedSha256 !== '') {
            $actual = hash_file('sha256', $path);
            if (!is_string($actual) || !hash_equals(strtolower($expectedSha256), strtolower($actual))) {
                $errors[] = 'Checksum mismatch.';
            }
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            $errors[] = 'Archive could not be opened.';
            return new VerificationResult(false, $errors);
        }

        foreach ($expectedEntries as $entry) {
            if ($zip->locateName($entry) === false) {
                $errors[] = "Expected archive entry missing: {$entry}";
            }
        }

        $zip->close();

        return new VerificationResult(empty($errors), $errors);
    }
}
