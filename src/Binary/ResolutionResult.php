<?php

namespace Amohamed\NativePhpCustomPhp\Binary;

final readonly class ResolutionResult
{
    public function __construct(
        public string $type,
        public ?string $path = null,
        public ?string $url = null,
        public ?string $sha256 = null,
        public ?int $size = null,
        public array $meta = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'path' => $this->path,
            'url' => $this->url,
            'sha256' => $this->sha256,
            'size' => $this->size,
            'meta' => $this->meta,
        ];
    }
}
