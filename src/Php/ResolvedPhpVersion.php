<?php

namespace Amohamed\NativePhpCustomPhp\Php;

final readonly class ResolvedPhpVersion
{
    /**
     * @param array<int, array<string, mixed>> $sources
     */
    public function __construct(
        public string $requested,
        public string $minor,
        public string $resolved,
        public bool $exactRequested,
        public bool $fromNetwork,
        public array $sources = [],
    ) {
    }
}
