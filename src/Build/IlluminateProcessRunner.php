<?php

namespace Amohamed\NativePhpCustomPhp\Build;

use Illuminate\Support\Facades\Process;
use RuntimeException;

final class IlluminateProcessRunner implements ProcessRunner
{
    public function run(array $command, ?string $workingDirectory = null): void
    {
        $pending = Process::path($workingDirectory ?? base_path());
        $result = $pending->run($command);

        if (!$result->successful()) {
            throw new RuntimeException('Process failed: ' . $result->errorOutput());
        }
    }
}
