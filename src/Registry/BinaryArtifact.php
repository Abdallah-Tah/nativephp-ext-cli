<?php

namespace Amohamed\NativePhpCustomPhp\Registry;

final readonly class BinaryArtifact
{
    /**
     * @param array<int, string> $extensions
     */
    public function __construct(
        public string $php,
        public string $phpMinor,
        public string $platform,
        public string $profile,
        public array $extensions,
        public string $url,
        public string $sha256,
        public int $size,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            php: (string) ($payload['php'] ?? ''),
            phpMinor: (string) ($payload['php_minor'] ?? ''),
            platform: (string) ($payload['platform'] ?? ''),
            profile: (string) ($payload['profile'] ?? 'nativephp'),
            extensions: array_values(array_map('strval', $payload['extensions'] ?? [])),
            url: (string) ($payload['url'] ?? ''),
            sha256: (string) ($payload['sha256'] ?? ''),
            size: (int) ($payload['size'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'php' => $this->php,
            'php_minor' => $this->phpMinor,
            'platform' => $this->platform,
            'profile' => $this->profile,
            'extensions' => $this->extensions,
            'url' => $this->url,
            'sha256' => $this->sha256,
            'size' => $this->size,
        ];
    }
}
