<?php

namespace Amohamed\NativePhpCustomPhp\Build;

final readonly class BuildSpec
{
    /**
     * @param array<int, string> $drivers
     * @param array<int, string> $extensions
     * @param array<int, string> $buildFlags
     */
    public function __construct(
        public string $phpVersion,
        public string $platform,
        public string $architecture,
        public array $drivers,
        public array $extensions,
        public string $profile,
        public array $buildFlags = [],
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return (new self(
            phpVersion: (string) ($payload['php_version'] ?? $payload['php'] ?? ''),
            platform: (string) ($payload['platform'] ?? ''),
            architecture: (string) ($payload['architecture'] ?? ''),
            drivers: array_values(array_map('strval', $payload['drivers'] ?? [])),
            extensions: array_values(array_map('strval', $payload['extensions'] ?? [])),
            profile: (string) ($payload['profile'] ?? 'nativephp'),
            buildFlags: array_values(array_map('strval', $payload['build_flags'] ?? [])),
        ))->normalized();
    }

    public function normalized(): self
    {
        return new self(
            phpVersion: strtolower(trim($this->phpVersion)),
            platform: strtolower(trim($this->platform)),
            architecture: strtolower(trim($this->architecture)),
            drivers: $this->normalizeList($this->drivers),
            extensions: $this->normalizeList($this->extensions),
            profile: strtolower(trim($this->profile)),
            buildFlags: $this->normalizeList($this->buildFlags),
        );
    }

    public function toArray(): array
    {
        return [
            'php' => $this->phpVersion,
            'php_version' => $this->phpVersion,
            'platform' => $this->platform,
            'architecture' => $this->architecture,
            'profile' => $this->profile,
            'drivers' => $this->drivers,
            'extensions' => $this->extensions,
            'build_flags' => $this->buildFlags,
            'build_hash' => $this->buildHash(),
        ];
    }

    public function toJson(): string
    {
        return (string) json_encode($this->toArray(), JSON_UNESCAPED_SLASHES);
    }

    public function buildHash(): string
    {
        return hash('sha256', $this->deterministicConfigurationPayload());
    }

    public function deterministicConfigurationPayload(): string
    {
        return (string) json_encode([
            'php' => $this->phpVersion,
            'platform' => $this->platform,
            'architecture' => $this->architecture,
            'profile' => $this->profile,
            'drivers' => $this->drivers,
            'extensions' => $this->extensions,
            'build_flags' => $this->buildFlags,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array<int, string> $values
     * @return array<int, string>
     */
    private function normalizeList(array $values): array
    {
        $normalized = [];

        foreach ($values as $value) {
            $value = strtolower(trim((string) $value));
            if ($value !== '' && !in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        sort($normalized);

        return $normalized;
    }
}
