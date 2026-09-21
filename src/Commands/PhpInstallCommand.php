<?php

namespace Amohamed\NativePhpCustomPhp\Commands;

use Amohamed\NativePhpCustomPhp\NativePhpBinaryManager;
use Illuminate\Console\Command;

final class PhpInstallCommand extends Command
{
    protected $signature = 'nativephp:php:install
        {--php= : PHP version (e.g. 8.5 or 8.5.3)}
        {--profile=nativephp : Profile name}
        {--driver=* : Database driver(s) to include}
        {--extensions= : Comma-separated custom extension list}
        {--build-flag=* : Build flags}
        {--dry-run : Resolve build and exit without installing}
        {--json : Emit machine-readable JSON output}
    ';

    protected $description = 'Install or resolve a NativePHP-compatible PHP binary';

    public function handle(NativePhpBinaryManager $manager): int
    {
        $php = (string) ($this->option('php') ?: config('nativephp-custom-php.default_php_version', '8.5'));
        $profile = (string) ($this->option('profile') ?: 'nativephp');
        $drivers = array_values((array) $this->option('driver'));
        $extensions = [];

        $extensionInput = $this->option('extensions');
        if (is_string($extensionInput) && $extensionInput !== '') {
            $extensions = array_values(array_filter(array_map('trim', explode(',', $extensionInput))));
        }

        $buildFlags = array_values((array) $this->option('build-flag'));
        try {
            $spec = $manager->buildSpec($php, $profile, $drivers, $extensions, $buildFlags);

            $result = (bool) $this->option('dry-run')
                ? ['status' => 'dry-run', 'spec' => $spec->toArray(), 'resolution' => $manager->resolve($spec)]
                : ['status' => 'install-required', 'spec' => $spec->toArray(), 'resolution' => $manager->install($spec)];
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
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $this->info('Profile: ' . $spec->profile);
        $this->line('PHP: ' . $spec->phpVersion);
        $this->line('Platform: ' . $spec->platform . '/' . $spec->architecture);
        $this->line('Drivers: ' . implode(', ', $spec->drivers));
        $this->line('Extensions: ' . implode(', ', $spec->extensions));
        $this->line('Resolution: ' . ($result['resolution']['type'] ?? 'unknown'));

        return self::SUCCESS;
    }
}
