<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Install;

use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\Foundation\Install\AuditInstallStep;
use YezzMedia\Foundation\Install\OptionalInstallStep;
use YezzMedia\OpsSettings\Support\OpsSettingsStoreSetup;

final readonly class ConfigureOpsSettingsAuditInstallStep implements AuditInstallStep, OptionalInstallStep
{
    public function __construct(private OpsSettingsStoreSetup $setup) {}

    public function key(): string
    {
        return 'configure_ops_settings_audit';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-ops-settings';
    }

    public function priority(): int
    {
        return 25;
    }

    public function shouldRun(InstallContext $context): bool
    {
        return $context->shouldConfigureAuditFor($this->package());
    }

    public function handle(InstallContext $context): void
    {
        $this->setup->configureAuditDriver('activitylog');
    }

    public function isOptional(): bool
    {
        return true;
    }
}
