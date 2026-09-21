<?php

namespace Amohamed\NativePhpCustomPhp\Php;

interface PhpReleaseLookup
{
    /**
     * @return array{version:string,sources:array<int,array<string,mixed>>}|null
     */
    public function latestReleaseForMinor(string $minor): ?array;
}
