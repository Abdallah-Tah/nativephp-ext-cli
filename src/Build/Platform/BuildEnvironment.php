<?php

namespace Amohamed\NativePhpCustomPhp\Build\Platform;

interface BuildEnvironment
{
    public function name(): string;

    /**
     * @return array<int, string>
     */
    public function prerequisites(): array;
}
