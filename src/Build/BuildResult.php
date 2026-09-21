<?php

namespace Amohamed\NativePhpCustomPhp\Build;

final readonly class BuildResult
{
    public function __construct(
        public bool $success,
        public ?string $artifactPath = null,
        public array $meta = [],
    ) {
    }
}
