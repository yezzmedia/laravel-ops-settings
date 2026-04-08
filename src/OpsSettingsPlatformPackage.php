<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings;

use YezzMedia\Foundation\Contracts\DefinesAuditEvents;
use YezzMedia\Foundation\Contracts\DefinesInstallSteps;
use YezzMedia\Foundation\Contracts\DefinesPermissions;
use YezzMedia\Foundation\Contracts\DefinesSecurityRequests;
use YezzMedia\Foundation\Contracts\DefinesSecurityRequirements;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Contracts\ProvidesDoctorChecks;
use YezzMedia\Foundation\Contracts\ProvidesOpsModules;
use YezzMedia\Foundation\Contracts\RegistersFeatures;
use YezzMedia\Foundation\Data\AuditEventDefinition;
use YezzMedia\Foundation\Data\FeatureDefinition;
use YezzMedia\Foundation\Data\OpsModuleDefinition;
use YezzMedia\Foundation\Data\PackageMetadata;
use YezzMedia\Foundation\Data\PermissionDefinition;
use YezzMedia\Foundation\Data\SecurityRequestDefinition;
use YezzMedia\Foundation\Data\SecurityRequirementDefinition;
use YezzMedia\Foundation\Doctor\DoctorCheck;
use YezzMedia\Foundation\Install\InstallStep;
use YezzMedia\OpsSettings\Doctor\OpsSettingsAuditConfiguredCheck;
use YezzMedia\OpsSettings\Doctor\OpsSettingsCompletenessCheck;
use YezzMedia\OpsSettings\Doctor\OpsSettingsConsistencyCheck;
use YezzMedia\OpsSettings\Doctor\OpsSettingsStoreReadyCheck;
use YezzMedia\OpsSettings\Install\ConfigureOpsSettingsAuditInstallStep;
use YezzMedia\OpsSettings\Install\EnsureOpsSettingsStoreReadyInstallStep;
use YezzMedia\OpsSettings\Install\PublishOpsSettingsMigrationsInstallStep;
use YezzMedia\OpsSettings\Install\SeedOpsSettingsDefaultsInstallStep;

/**
 * Describes the ops-settings package surface that foundation should register.
 */
final class OpsSettingsPlatformPackage implements DefinesAuditEvents, DefinesInstallSteps, DefinesPermissions, DefinesSecurityRequests, DefinesSecurityRequirements, PlatformPackage, ProvidesDoctorChecks, ProvidesOpsModules, RegistersFeatures
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
     * @return array<int, FeatureDefinition>
     */
    public function featureDefinitions(): array
    {
        return [
            new FeatureDefinition(
                'settings.identity',
                'yezzmedia/laravel-ops-settings',
                'Identity settings',
                'Manages operator-facing identity labels and platform naming defaults.',
            ),
            new FeatureDefinition(
                'settings.contact',
                'yezzmedia/laravel-ops-settings',
                'Contact settings',
                'Manages support contact details and postal address defaults for the platform.',
            ),
            new FeatureDefinition(
                'settings.brand',
                'yezzmedia/laravel-ops-settings',
                'Brand settings',
                'Manages shared brand copy, palette defaults, and reusable visual references.',
            ),
            new FeatureDefinition(
                'settings.social',
                'yezzmedia/laravel-ops-settings',
                'Social settings',
                'Manages linked public social profiles and channel defaults.',
            ),
            new FeatureDefinition(
                'settings.legal',
                'yezzmedia/laravel-ops-settings',
                'Legal settings',
                'Manages legal entity details, registrations, and legal notice content.',
            ),
            new FeatureDefinition(
                'settings.website_defaults',
                'yezzmedia/laravel-ops-settings',
                'Website defaults',
                'Manages reusable site-wide title, footer, and support-label defaults.',
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
                contextKeys: ['group', 'changed_keys', 'actor_id', 'old_values', 'new_values', 'context', 'source'],
            ),
            new AuditEventDefinition(
                key: 'ops.settings.snapshot_exported',
                package: 'yezzmedia/laravel-ops-settings',
                action: 'exported',
                subjectType: 'ops_settings_snapshot',
                description: 'An ops settings snapshot was exported.',
                severity: 'info',
                contextKeys: ['completion_percent', 'group_count', 'actor_id', 'exported_at', 'source'],
            ),
            new AuditEventDefinition(
                key: 'ops.settings.snapshot_imported',
                package: 'yezzmedia/laravel-ops-settings',
                action: 'imported',
                subjectType: 'ops_settings_snapshot',
                description: 'An ops settings snapshot was imported into the workspace.',
                severity: 'info',
                contextKeys: ['imported_groups', 'imported_group_count', 'actor_id', 'source'],
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
            app(ConfigureOpsSettingsAuditInstallStep::class),
            app(EnsureOpsSettingsStoreReadyInstallStep::class),
            app(SeedOpsSettingsDefaultsInstallStep::class),
        ];
    }

    /**
     * @return array<int, DoctorCheck>
     */
    public function doctorChecks(): array
    {
        return [
            app(OpsSettingsAuditConfiguredCheck::class),
            app(OpsSettingsCompletenessCheck::class),
            app(OpsSettingsConsistencyCheck::class),
            app(OpsSettingsStoreReadyCheck::class),
        ];
    }

    /**
     * @return array<int, OpsModuleDefinition>
     */
    public function opsModuleDefinitions(): array
    {
        return [
            new OpsModuleDefinition('content.settings.identity', 'yezzmedia/laravel-ops-settings', 'Identity', 'page', 'ops.settings.view'),
            new OpsModuleDefinition('content.settings.contact', 'yezzmedia/laravel-ops-settings', 'Contact', 'page', 'ops.settings.view'),
            new OpsModuleDefinition('content.settings.brand', 'yezzmedia/laravel-ops-settings', 'Brand', 'page', 'ops.settings.view'),
            new OpsModuleDefinition('content.settings.social', 'yezzmedia/laravel-ops-settings', 'Social', 'page', 'ops.settings.view'),
            new OpsModuleDefinition('content.settings.legal', 'yezzmedia/laravel-ops-settings', 'Legal', 'page', 'ops.settings.view'),
            new OpsModuleDefinition('content.settings.website_defaults', 'yezzmedia/laravel-ops-settings', 'Website Defaults', 'page', 'ops.settings.view'),
        ];
    }

    /**
     * @return array<int, SecurityRequestDefinition>
     */
    public function securityRequestDefinitions(): array
    {
        return [
            new SecurityRequestDefinition(
                key: 'ops-settings.request.auth.password-confirmation',
                package: 'yezzmedia/laravel-ops-settings',
                domain: 'auth',
                control: 'password_confirmation',
                scope: 'destructive-settings',
                requestedLevel: 'required',
                requestedEnforcementMode: 'package_owned',
                description: 'Critical ops settings mutations should require password confirmation before destructive actions run.',
                payloadSchema: [
                    'surface' => 'Ops settings page or action surface.',
                    'action' => 'Mutation action name.',
                    'permission' => 'Required permission for the action.',
                ],
                allowedPreviewFields: ['surface', 'action', 'permission'],
                notes: 'Action-level confirmation remains package-owned while ops-security verifies that the declared hardening exists.',
            ),
        ];
    }

    /**
     * @return array<int, SecurityRequirementDefinition>
     */
    public function securityRequirementDefinitions(): array
    {
        return [
            new SecurityRequirementDefinition(
                key: 'ops-settings.auth.password-confirmation',
                package: 'yezzmedia/laravel-ops-settings',
                domain: 'auth',
                control: 'password_confirmation',
                level: 'required',
                scope: 'destructive-settings',
                description: 'Destructive ops settings changes should require explicit password confirmation.',
                enforcementMode: 'package_owned',
                appliesTo: ['settings-mutations', 'snapshot-import', 'preset-apply'],
                notes: 'The verification layer may report missing protection, but the confirmation UX stays near the mutating ops-settings actions.',
            ),
        ];
    }
}
