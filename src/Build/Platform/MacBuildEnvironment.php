<?php

namespace Amohamed\NativePhpCustomPhp\Build\Platform;

final class MacBuildEnvironment implements BuildEnvironment
{
    public function name(): string
    {
        return 'mac';
    }

    public function prerequisites(): array
    {
        return ['xcode-command-line-tools', 'brew'];
    }
}
