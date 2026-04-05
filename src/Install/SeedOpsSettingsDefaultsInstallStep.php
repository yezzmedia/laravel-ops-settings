<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Install;

use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Install\InstallStep;
use YezzMedia\OpsSettings\Database\Seeders\OpsSettingsDefaultsSeeder;
use YezzMedia\OpsSettings\Support\OpsSettingsStoreSetup;

final class SeedOpsSettingsDefaultsInstallStep implements InstallStep
{
    public function __construct(private readonly OpsSettingsStoreSetup $setup) {}

    public function key(): string
    {
        return 'seed_ops_settings_defaults';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-ops-settings';
    }

    public function priority(): int
    {
        return 40;
    }

    public function shouldRun(InstallContext $context): bool
    {
        return config('ops-settings.defaults.seed_on_install', true) === true
            && $this->setup->storeReady();
    }

    public function handle(InstallContext $context): void
    {
        app(OpsSettingsDefaultsSeeder::class)->run();
    }
}
