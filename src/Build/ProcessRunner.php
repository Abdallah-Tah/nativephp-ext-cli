<?php

namespace Amohamed\NativePhpCustomPhp\Build;

interface ProcessRunner
{
    /**
     * @param array<int, string> $command
     */
    public function run(array $command, ?string $workingDirectory = null): void;
}
