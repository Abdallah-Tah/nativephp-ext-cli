<?php

namespace Amohamed\NativePhpCustomPhp\Platform;

final readonly class PlatformInfo
{
    public function __construct(
        public string $platform,
        public string $architecture,
        public string $family,
        public string $machine,
    ) {
    }

    public function toArray(): array
    {
        return [
            'platform' => $this->platform,
            'architecture' => $this->architecture,
            'family' => $this->family,
            'machine' => $this->machine,
        ];
    }

    public function key(): string
    {
        return $this->platform . '-' . $this->architecture;
    }
}
