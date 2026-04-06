<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Events\Dispatcher;
use InvalidArgumentException;
use Spatie\Activitylog\Support\ActivityLogger;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;
use YezzMedia\Foundation\Support\PlatformPackageRegistrar;
use YezzMedia\OpsSettings\Actions\UpdateOpsSettingsAction;
use YezzMedia\OpsSettings\Contracts\OpsSettingsAuditWriter;
use YezzMedia\OpsSettings\Doctor\OpsSettingsAuditConfiguredCheck;
use YezzMedia\OpsSettings\Doctor\OpsSettingsStoreReadyCheck;
use YezzMedia\OpsSettings\Events\OpsSettingsUpdated;
use YezzMedia\OpsSettings\Install\ConfigureOpsSettingsAuditInstallStep;
use YezzMedia\OpsSettings\Listeners\OpsSettingsAuditListener;
use YezzMedia\OpsSettings\Support\ActivityLogOpsSettingsAuditWriter;
use YezzMedia\OpsSettings\Support\NullOpsSettingsAuditWriter;
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
            ->hasConfigFile('ops-settings')
            ->hasMigrations([
                '0001_add_operator_identity_settings',
                '0002_add_platform_contact_settings',
                '0003_add_platform_brand_settings',
                '0004_add_platform_social_settings',
                '0005_add_platform_legal_settings',
                '0006_add_platform_website_defaults_settings',
                '0007_add_extended_ops_settings_fields',
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(OpsSettingsAuditWriter::class, fn (): OpsSettingsAuditWriter => $this->makeAuditWriter());
        $this->app->singleton(OpsSettingsAuditConfiguredCheck::class);
        $this->app->singleton(ConfigureOpsSettingsAuditInstallStep::class);
        $this->app->singleton(OpsSettingsStoreSetup::class);
        $this->app->singleton(OpsSettingsStoreReadyCheck::class);

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
                repository: $this->app->make(SettingsRepository::class),
            );
        });
    }

    public function packageBooted(): void
    {
        $this->app->make(PlatformPackageRegistrar::class)->register(new OpsSettingsPlatformPackage);
        $this->registerAuditListeners($this->app->make(Dispatcher::class));
    }

    private function registerAuditListeners(Dispatcher $events): void
    {
        $events->listen(OpsSettingsUpdated::class, [OpsSettingsAuditListener::class, 'handleSettingsUpdated']);
    }

    private function makeAuditWriter(): OpsSettingsAuditWriter
    {
        $driver = config('ops-settings.audit.driver');

        if ($driver === null) {
            return new NullOpsSettingsAuditWriter;
        }

        if ($driver !== 'activitylog') {
            throw new InvalidArgumentException(sprintf('Unsupported ops settings audit driver [%s].', $driver));
        }

        if (! class_exists('Spatie\\Activitylog\\ActivitylogServiceProvider') || ! class_exists(ActivityLogger::class)) {
            throw new InvalidArgumentException('Ops settings audit driver [activitylog] requires spatie/laravel-activitylog.');
        }

        return new ActivityLogOpsSettingsAuditWriter($this->app->make(ActivityLogger::class));
    }
}
