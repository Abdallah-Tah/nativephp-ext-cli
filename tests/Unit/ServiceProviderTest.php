<?php

namespace Amohamed\NativePhpCustomPhp\Tests\Unit;

use Amohamed\NativePhpCustomPhp\NativePhpCustomPhpServiceProvider;
use Illuminate\Contracts\Console\Kernel;
use Orchestra\Testbench\TestCase;

class ServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [NativePhpCustomPhpServiceProvider::class];
    }

    public function test_service_provider_is_registered()
    {
        $this->assertTrue($this->app->providerIsLoaded(NativePhpCustomPhpServiceProvider::class));
    }

    public function test_registers_nativephp_commands(): void
    {
        $kernel = $this->app->make(Kernel::class);
        $commands = $kernel->all();

        $this->assertArrayHasKey('nativephp:php:list', $commands);
        $this->assertArrayHasKey('nativephp:php:doctor', $commands);
        $this->assertArrayHasKey('nativephp:php:install', $commands);
    }
}