<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Install;

use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Install\InstallStep;
use YezzMedia\OpsSettings\Support\OpsSettingsStoreSetup;

final class PublishOpsSettingsMigrationsInstallStep implements InstallStep
{
    public function __construct(private readonly OpsSettingsStoreSetup $setup) {}

    public function key(): string
    {
        return 'publish_ops_settings_migrations';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-ops-settings';
    }

    public function priority(): int
    {
        return 20;
    }

    public function shouldRun(InstallContext $context): bool
    {
        return $context->refreshPublishedResources || ! $this->setup->migrationsPublished();
    }

    public function handle(InstallContext $context): void
    {
        $this->setup->publishMigrations(force: $context->refreshPublishedResources);
    }
}
