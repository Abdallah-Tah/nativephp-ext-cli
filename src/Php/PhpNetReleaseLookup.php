<?php

namespace Amohamed\NativePhpCustomPhp\Php;

final class PhpNetReleaseLookup implements PhpReleaseLookup
{
    public function latestReleaseForMinor(string $minor): ?array
    {
        $url = "https://www.php.net/releases/?json&version={$minor}";
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return null;
        }

        /** @var mixed $decoded */
        $decoded = json_decode($response, true);

        if (!is_array($decoded) || empty($decoded['version']) || !is_string($decoded['version'])) {
            return null;
        }

        $sources = [];

        if (isset($decoded['source']) && is_array($decoded['source'])) {
            foreach ($decoded['source'] as $source) {
                if (is_array($source) && isset($source['filename']) && is_string($source['filename'])) {
                    $sources[] = $source;
                }
            }
        }

        return [
            'version' => $decoded['version'],
            'sources' => $sources,
        ];
    }
}
