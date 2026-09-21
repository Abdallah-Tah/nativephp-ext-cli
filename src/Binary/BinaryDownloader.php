<?php

namespace Amohamed\NativePhpCustomPhp\Binary;

use RuntimeException;

final class BinaryDownloader
{
    public function downloadVerified(string $url, string $targetPath, ?string $expectedSha256 = null, ?int $expectedSize = null, int $retries = 2): string
    {
        if (!str_starts_with($url, 'https://')) {
            throw new RuntimeException('Only HTTPS downloads are allowed.');
        }

        $attempts = 0;
        $lastException = null;

        while ($attempts <= $retries) {
            $attempts++;
            $tempPath = $targetPath . '.tmp-' . uniqid('', true);

            try {
                $this->streamDownload($url, $tempPath);
                $this->verify($tempPath, $expectedSha256, $expectedSize);

                $directory = dirname($targetPath);
                if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
                    throw new RuntimeException('Failed to create target directory: ' . $directory);
                }

                if (file_exists($targetPath) && !@unlink($targetPath)) {
                    throw new RuntimeException('Failed to replace existing cached artifact.');
                }

                if (!@rename($tempPath, $targetPath)) {
                    throw new RuntimeException('Failed to move artifact into cache.');
                }

                return $targetPath;
            } catch (\Throwable $exception) {
                $lastException = $exception;
                if (file_exists($tempPath)) {
                    @unlink($tempPath);
                }
            }
        }

        throw new RuntimeException('Failed to download binary artifact after retries.', previous: $lastException);
    }

    private function streamDownload(string $url, string $tempPath): void
    {
        $in = @fopen($url, 'rb', false, stream_context_create([
            'http' => [
                'timeout' => 20,
                'max_redirects' => 3,
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]));

        if ($in === false) {
            throw new RuntimeException('Failed to open download URL.');
        }

        $out = @fopen($tempPath, 'wb');

        if ($out === false) {
            fclose($in);
            throw new RuntimeException('Failed to create temporary file.');
        }

        stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);
    }

    private function verify(string $path, ?string $expectedSha256, ?int $expectedSize): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException('Downloaded file is missing.');
        }

        if ($expectedSize !== null && filesize($path) !== $expectedSize) {
            throw new RuntimeException('Downloaded file size mismatch.');
        }

        if ($expectedSha256 !== null && $expectedSha256 !== '') {
            $actual = hash_file('sha256', $path);
            if (!is_string($actual) || !hash_equals(strtolower($expectedSha256), strtolower($actual))) {
                throw new RuntimeException('Downloaded file checksum mismatch.');
            }
        }
    }
}
