<?php

namespace Amohamed\NativePhpCustomPhp\Build\Platform;

final class WindowsBuildEnvironment implements BuildEnvironment
{
    public function name(): string
    {
        return 'windows';
    }

    public function prerequisites(): array
    {
        return ['visual-studio-build-tools', 'strawberry-perl', 'cmake', 'python', 'php-sdk-binary-tools'];
    }
}
