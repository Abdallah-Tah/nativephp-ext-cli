<?php

namespace Amohamed\NativePhpCustomPhp\Binary;

final readonly class VerificationResult
{
    /**
     * @param array<int, string> $errors
     */
    public function __construct(
        public bool $valid,
        public array $errors = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->valid,
            'errors' => $this->errors,
        ];
    }
}
