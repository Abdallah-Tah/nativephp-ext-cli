<?php

namespace Amohamed\NativePhpCustomPhp\Commands;

use Amohamed\NativePhpCustomPhp\NativePhpBinaryManager;
use Illuminate\Console\Command;

final class PhpListCommand extends Command
{
    protected $signature = 'nativephp:php:list {--json : Emit machine-readable JSON output}';

    protected $description = 'List supported PHP versions, profiles, and drivers for NativePHP binary management';

    public function handle(NativePhpBinaryManager $manager): int
    {
        try {
            $payload = [
                'php_versions' => $manager->availablePhpVersions(),
                'profiles' => $manager->availableProfiles(),
                'doctor' => $manager->doctor(),
            ];
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
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $this->info('Supported PHP Versions:');
        foreach ($payload['php_versions'] as $minor => $patch) {
            $this->line("- {$minor} ({$patch})");
        }

        $this->newLine();
        $this->info('Profiles: ' . implode(', ', $payload['profiles']));
        $this->line('Drivers: ' . implode(', ', $payload['doctor']['drivers']));

        return self::SUCCESS;
    }
}
