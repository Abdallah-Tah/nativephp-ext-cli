<?php

namespace Amohamed\NativePhpCustomPhp\Profiles;

final readonly class Profile
{
    /**
     * @param array<int, string> $drivers
     * @param array<int, string> $extensions
     */
    public function __construct(
        public string $name,
        public array $drivers,
        public array $extensions,
    ) {
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'drivers' => $this->drivers,
            'extensions' => $this->extensions,
        ];
    }
}
