<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings;

use YezzMedia\Foundation\Contracts\DefinesAuditEvents;
use YezzMedia\Foundation\Contracts\DefinesInstallSteps;
use YezzMedia\Foundation\Contracts\DefinesPermissions;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Contracts\ProvidesOpsModules;
use YezzMedia\Foundation\Data\AuditEventDefinition;
use YezzMedia\Foundation\Data\OpsModuleDefinition;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Data\PermissionDefinition;
use YezzMedia\Foundation\Install\InstallStep;
use YezzMedia\OpsSettings\Install\EnsureOpsSettingsStoreReadyInstallStep;
use YezzMedia\OpsSettings\Install\PublishOpsSettingsMigrationsInstallStep;
use YezzMedia\OpsSettings\Install\SeedOpsSettingsDefaultsInstallStep;

/**
 * Describes the ops-settings package surface that foundation should register.
 */
final class OpsSettingsPlatformPackage implements DefinesAuditEvents, DefinesInstallSteps, DefinesPermissions, PlatformPackage, ProvidesOpsModules
{
    public function metadata(): PackageMetadata
    {
        return new PackageMetadata(
            name: 'yezzmedia/laravel-ops-settings',
            vendor: 'yezzmedia',
            description: 'Operator-managed global platform settings for the Yezz Media Laravel website platform.',
            packageClass: self::class,
        );
    }

    /**
     * @return array<int, PermissionDefinition>
     */
    public function permissionDefinitions(): array
    {
        return [
            new PermissionDefinition(
                name: 'ops.settings.view',
                package: 'yezzmedia/laravel-ops-settings',
                label: 'View ops settings',
                description: 'Allows reading operator-managed global platform settings.',
            ),
            new PermissionDefinition(
                name: 'ops.settings.manage',
                package: 'yezzmedia/laravel-ops-settings',
                label: 'Manage ops settings',
                description: 'Allows mutating operator-managed global platform settings.',
            ),
        ];
    }

    /**
     * @return array<int, AuditEventDefinition>
     */
    public function auditEventDefinitions(): array
    {
        return [
            new AuditEventDefinition(
                key: 'ops.settings.updated',
                package: 'yezzmedia/laravel-ops-settings',
                action: 'updated',
                subjectType: 'ops_settings',
                description: 'Operator-managed global platform settings were updated.',
                severity: 'warning',
                contextKeys: ['group', 'changed_keys', 'actor_id', 'context', 'source'],
            ),
        ];
    }

    /**
     * @return array<int, InstallStep>
     */
    public function installSteps(): array
    {
        return [
            app(PublishOpsSettingsMigrationsInstallStep::class),
            app(EnsureOpsSettingsStoreReadyInstallStep::class),
            app(SeedOpsSettingsDefaultsInstallStep::class),
        ];
    }

    /**
     * @return array<int, OpsModuleDefinition>
     */
    public function opsModuleDefinitions(): array
    {
        return [];
    }
}
