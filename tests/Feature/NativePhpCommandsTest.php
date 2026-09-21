<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Feature;

use Orchestra\Testbench\TestCase;

class NativePhpCommandsTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return ['Amohamed\\NativePhpCustomPhp\\NativePhpCustomPhpServiceProvider'];
    }

    public function test_nativephp_php_list_command(): void
    {
        $this->artisan('nativephp:php:list --json')
            ->expectsOutputToContain('"php_versions"')
            ->expectsOutputToContain('"profiles"')
            ->assertExitCode(0);
    }

    public function test_nativephp_php_doctor_command(): void
    {
        $this->artisan('nativephp:php:doctor --json')
            ->expectsOutputToContain('"platform"')
            ->expectsOutputToContain('"php_version"')
            ->assertExitCode(0);
    }

    public function test_nativephp_php_install_dry_run_command(): void
    {
        $this->artisan('nativephp:php:install --php=8.5 --profile=mysql --dry-run --json')
            ->expectsOutputToContain('"status": "dry-run"')
            ->expectsOutputToContain('"resolution"')
            ->assertExitCode(0);
    }

    public function test_nativephp_php_install_resolve_command(): void
    {
        $this->artisan('nativephp:php:install --php=8.5 --profile=mysql --json')
            ->expectsOutputToContain('"status": "install-required"')
            ->assertExitCode(0);
    }

    public function test_nativephp_php_install_json_error_payload(): void
    {
        $this->artisan('nativephp:php:install --php=8.0 --profile=mysql --json')
            ->expectsOutputToContain('"status": "error"')
            ->expectsOutputToContain('"message"')
            ->assertExitCode(1);
    }
}
