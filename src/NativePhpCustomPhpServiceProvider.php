<?php

namespace Amohamed\NativePhpCustomPhp;

use Amohamed\NativePhpCustomPhp\Commands\InstallPhpExtensions;
use Amohamed\NativePhpCustomPhp\Commands\PhpDoctorCommand;
use Amohamed\NativePhpCustomPhp\Commands\PhpInstallCommand;
use Amohamed\NativePhpCustomPhp\Commands\PhpListCommand;
use Amohamed\NativePhpCustomPhp\Binary\BinaryResolver;
use Amohamed\NativePhpCustomPhp\Cache\BinaryCache;
use Amohamed\NativePhpCustomPhp\Drivers\DriverRegistry;
use Amohamed\NativePhpCustomPhp\Php\PhpNetReleaseLookup;
use Amohamed\NativePhpCustomPhp\Php\PhpVersionResolver;
use Amohamed\NativePhpCustomPhp\Platform\PlatformDetector;
use Amohamed\NativePhpCustomPhp\Profiles\ProfileRegistry;
use Amohamed\NativePhpCustomPhp\Registry\ManifestClient;
use Illuminate\Support\ServiceProvider;

class NativePhpCustomPhpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/nativephp-custom-php.php',
            'nativephp-custom-php'
        );

        $this->app->singleton(PhpVersionResolver::class, function () {
            return new PhpVersionResolver(new PhpNetReleaseLookup());
        });
        $this->app->singleton(PlatformDetector::class, fn () => new PlatformDetector());
        $this->app->singleton(DriverRegistry::class, fn () => new DriverRegistry());
        $this->app->singleton(ProfileRegistry::class, function ($app) {
            return new ProfileRegistry(driverRegistry: $app->make(DriverRegistry::class));
        });
        $this->app->singleton(BinaryCache::class, fn () => new BinaryCache());
        $this->app->singleton(ManifestClient::class, function ($app) {
            $httpFactory = null;

            if ($app->bound(\Illuminate\Http\Client\Factory::class)) {
                $httpFactory = $app->make(\Illuminate\Http\Client\Factory::class);
            }

            return new ManifestClient($httpFactory);
        });
        $this->app->singleton(BinaryResolver::class, function ($app) {
            return new BinaryResolver(
                $app->make(BinaryCache::class),
                $app->make(ManifestClient::class),
                (string) config('nativephp-custom-php.binary_registry_url', '')
            );
        });
        $this->app->singleton(NativePhpBinaryManager::class, function ($app) {
            return new NativePhpBinaryManager(
                $app->make(PhpVersionResolver::class),
                $app->make(PlatformDetector::class),
                $app->make(DriverRegistry::class),
                $app->make(ProfileRegistry::class),
                $app->make(BinaryResolver::class),
            );
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallPhpExtensions::class,
                PhpListCommand::class,
                PhpInstallCommand::class,
                PhpDoctorCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/config/nativephp-custom-php.php' => config_path('nativephp-custom-php.php'),
            ], 'nativephp-custom-php-config');
        }
    }

    public function boot()
    {
        // Make sure the config directory exists
        if (!is_dir(config_path())) {
            mkdir(config_path(), 0755, true);
        }
    }
}
