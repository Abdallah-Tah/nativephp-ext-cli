<?php

namespace Amohamed\NativePhpCustomPhp\Commands;

use Amohamed\NativePhpCustomPhp\NativePhpBinaryManager;
use Illuminate\Console\Command;

final class PhpDoctorCommand extends Command
{
    protected $signature = 'nativephp:php:doctor {--json : Emit machine-readable JSON output}';

    protected $description = 'Inspect local environment and NativePHP binary-manager readiness';

    public function handle(NativePhpBinaryManager $manager): int
    {
        try {
            $report = $manager->doctor();
        } catch (\Throwable $exception) {
            if ((bool) $this->option('json')) {
                $this->line((string) json_encode([
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                return self::FAILURE;
            }

            throw $exception;
        }

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $this->info('Platform: ' . $report['platform']);
        $this->line('Architecture: ' . $report['architecture']);
        $this->line('PHP: ' . $report['php_version']);
        $this->line('Drivers: ' . implode(', ', $report['drivers']));
        $this->line('Profiles: ' . implode(', ', $report['profiles']));

        return self::SUCCESS;
    }
}
