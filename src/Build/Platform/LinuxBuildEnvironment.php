<?php

namespace Amohamed\NativePhpCustomPhp\Build\Platform;

final class LinuxBuildEnvironment implements BuildEnvironment
{
    public function name(): string
    {
        return 'linux';
    }

    public function prerequisites(): array
    {
        return ['build-essential', 'autoconf', 'bison', 're2c'];
    }
}
