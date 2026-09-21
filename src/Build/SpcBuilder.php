<?php

namespace Amohamed\NativePhpCustomPhp\Build;

use Amohamed\NativePhpCustomPhp\Build\Platform\BuildEnvironment;

final class SpcBuilder
{
    public const PINNED_SPC_VERSION = '2.9.0';

    public function __construct(
        private readonly ProcessRunner $runner,
        private readonly BuildEnvironment $environment,
    ) {
    }

    public function pinnedVersion(): string
    {
        return self::PINNED_SPC_VERSION;
    }

    public function environment(): BuildEnvironment
    {
        return $this->environment;
    }

    public function build(BuildSpec $spec): BuildResult
    {
        // Local SPC build orchestration remains in InstallPhpExtensions for backward compatibility;
        // this class provides a testable seam for iterative extraction.
        return new BuildResult(false, null, [
            'reason' => 'local-build-not-yet-extracted',
            'spec' => $spec->toArray(),
            'spc_version' => self::PINNED_SPC_VERSION,
            'environment' => $this->environment->name(),
        ]);
    }
}
