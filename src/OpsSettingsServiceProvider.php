<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;
use YezzMedia\OpsSettings\Actions\UpdateOpsSettingsAction;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;
use YezzMedia\OpsSettings\Support\OpsSettingsStoreSetup;

/**
 * Bootstrap root for the ops-settings package.
 *
 * Bindings belong in packageRegistered().
 * Foundation registration belongs in packageBooted().
 * No settings state is mutated during ordinary runtime boot.
 */
class OpsSettingsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-ops-settings')
            ->hasConfigFile()
            ->hasMigrations([
                '0001_add_operator_identity_settings',
                '0002_add_platform_contact_settings',
                '0003_add_platform_brand_settings',
                '0004_add_platform_social_settings',
                '0005_add_platform_legal_settings',
                '0006_add_platform_website_defaults_settings',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(OpsSettingsStoreSetup::class);

        $this->app->singleton(OpsSettingsManager::class, function (): OpsSettingsManager {
            return new OpsSettingsManager(
                cacheFactory: $this->app->make(CacheFactory::class),
                cacheEnabled: (bool) config('ops-settings.cache.enabled', true),
                cacheStore: config('ops-settings.cache.store'),
            );
        });

        $this->app->singleton(UpdateOpsSettingsAction::class, function (): UpdateOpsSettingsAction {
            return new UpdateOpsSettingsAction(
                manager: $this->app->make(OpsSettingsManager::class),
            );
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(PlatformPackageRegistrar::class)->register(new OpsSettingsPlatformPackage);
    }
}
