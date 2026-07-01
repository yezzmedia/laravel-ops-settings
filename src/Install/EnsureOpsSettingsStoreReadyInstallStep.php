<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Install;

use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Install\InstallStep;
use YezzMedia\Foundation\Install\OptionalInstallStep;
use YezzMedia\OpsSettings\Support\OpsSettingsStoreSetup;

final class EnsureOpsSettingsStoreReadyInstallStep implements InstallStep, OptionalInstallStep
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
            fwrite(
                STDERR,
                "\n  \033[33;1mWARNING\033[39;22m  Ops settings store is not ready. Run 'php artisan migrate' or 'php artisan website:install --migrate'.\n\n"
            );

            return;
        }

        try {
            $this->setup->runMigrations();
        } catch (\Throwable) {
            // Migration may fail if tables already exist
        }

        if (! $this->setup->storeReady()) {
            fwrite(
                STDERR,
                "\n  \033[33;1mWARNING\033[39;22m  Ops settings store could not be created. Check your database configuration.\n\n"
            );
        }
    }

    public function isOptional(): bool
    {
        return true;
    }
}
