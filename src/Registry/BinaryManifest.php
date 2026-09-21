<?php

namespace Amohamed\NativePhpCustomPhp\Registry;

use RuntimeException;

final readonly class BinaryManifest
{
    /**
     * @param array<int, BinaryArtifact> $artifacts
     */
    public function __construct(
        public int $schema,
        public string $generatedAt,
        public array $artifacts,
    ) {
    }

    public static function fromJson(string $json): self
    {
        /** @var mixed $decoded */
        $decoded = json_decode($json, true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid binary manifest JSON.');
        }

        return self::fromArray($decoded);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        $artifacts = [];

        foreach (($payload['artifacts'] ?? []) as $artifact) {
            if (is_array($artifact)) {
                $artifacts[] = BinaryArtifact::fromArray($artifact);
            }
        }

        return new self(
            schema: (int) ($payload['schema'] ?? 1),
            generatedAt: (string) ($payload['generated_at'] ?? ''),
            artifacts: $artifacts,
        );
    }

    public function toArray(): array
    {
        return [
            'schema' => $this->schema,
            'generated_at' => $this->generatedAt,
            'artifacts' => array_map(static fn (BinaryArtifact $artifact): array => $artifact->toArray(), $this->artifacts),
        ];
    }
}
