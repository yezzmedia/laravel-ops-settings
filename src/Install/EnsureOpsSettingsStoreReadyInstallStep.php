<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Install;

use RuntimeException;
use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Install\InstallStep;
use YezzMedia\OpsSettings\Support\OpsSettingsStoreSetup;

final class EnsureOpsSettingsStoreReadyInstallStep implements InstallStep
{
    public function __construct(private readonly OpsSettingsStoreSetup $setup) {}

    public function key(): string
    {
        return 'ensure_ops_settings_store_ready';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-ops-settings';
    }

    public function priority(): int
    {
        return 30;
    }

    public function shouldRun(InstallContext $context): bool
    {
        return ! $this->setup->storeReady() || $this->setup->hasPendingMigrations();
    }

    public function handle(InstallContext $context): void
    {
        if (! $context->allowMigrations) {
            throw new RuntimeException(
                'The ops settings store is not ready or has pending settings migrations. '
                .'Run `php artisan migrate` or rerun the install command with `--migrate`.',
            );
        }

        $this->setup->runMigrations();

        if (! $this->setup->storeReady()) {
            throw new RuntimeException('The ops settings store is still not ready after running migrations.');
        }
    }
}
