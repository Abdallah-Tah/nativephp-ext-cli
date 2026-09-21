<?php

namespace Amohamed\NativePhpCustomPhp\Registry;

use Illuminate\Http\Client\Factory as HttpFactory;

final class ManifestClient
{
    public function __construct(
        private readonly ?HttpFactory $http = null,
    ) {
    }

    public function fetch(string $url): ?BinaryManifest
    {
        if ($url === '' || !str_starts_with($url, 'https://')) {
            return null;
        }

        if ($this->http === null) {
            $payload = @file_get_contents($url, false, stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'max_redirects' => 3,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]));
            if ($payload === false) {
                return null;
            }

            try {
                return BinaryManifest::fromJson($payload);
            } catch (\Throwable) {
                return null;
            }
        }

        $response = $this->http->retry(2, 100)->get($url);

        if (!$response->successful()) {
            return null;
        }

        try {
            return BinaryManifest::fromJson($response->body());
        } catch (\Throwable) {
            return null;
        }
    }
}
