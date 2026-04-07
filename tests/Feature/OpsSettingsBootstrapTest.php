<?php

declare(strict_types=1);

use YezzMedia\Foundation\Contracts\DefinesAuditEvents;
use YezzMedia\Foundation\Contracts\DefinesInstallSteps;
use YezzMedia\Foundation\Contracts\DefinesPermissions;
use YezzMedia\Foundation\Contracts\PlatformPackage;
use YezzMedia\Foundation\Contracts\ProvidesDoctorChecks;
use YezzMedia\Foundation\Contracts\ProvidesOpsModules;
use YezzMedia\Foundation\Contracts\RegistersFeatures;
use YezzMedia\Foundation\Doctor\DoctorManager;
use YezzMedia\Foundation\Registry\FeatureRegistry;
use YezzMedia\Foundation\Registry\OpsModuleRegistry;
use YezzMedia\Foundation\Registry\PackageRegistry;
use YezzMedia\Foundation\Registry\PermissionRegistry;
use YezzMedia\OpsSettings\Actions\UpdateOpsSettingsAction;
use YezzMedia\OpsSettings\Contracts\OpsSettingsAuditWriter;
use YezzMedia\OpsSettings\Doctor\OpsSettingsAuditConfiguredCheck;
use YezzMedia\OpsSettings\Doctor\OpsSettingsCompletenessCheck;
use YezzMedia\OpsSettings\Doctor\OpsSettingsConsistencyCheck;
use YezzMedia\OpsSettings\Doctor\OpsSettingsStoreReadyCheck;
use YezzMedia\OpsSettings\Install\ConfigureOpsSettingsAuditInstallStep;
use YezzMedia\OpsSettings\Install\EnsureOpsSettingsStoreReadyInstallStep;
use YezzMedia\OpsSettings\Install\PublishOpsSettingsMigrationsInstallStep;
use YezzMedia\OpsSettings\Install\SeedOpsSettingsDefaultsInstallStep;
use YezzMedia\OpsSettings\OpsSettingsPlatformPackage;
use YezzMedia\OpsSettings\OpsSettingsServiceProvider;
use YezzMedia\OpsSettings\Support\NullOpsSettingsAuditWriter;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

it('registers the ops-settings bootstrap bindings', function (): void {
    $doctorResults = app(DoctorManager::class)->run()->keyBy('key');

    expect(app(PackageRegistry::class)->has('yezzmedia/laravel-ops-settings'))->toBeTrue()
        ->and(app(OpsSettingsAuditWriter::class))->toBeInstanceOf(NullOpsSettingsAuditWriter::class)
        ->and(app(PermissionRegistry::class)->forPackage('yezzmedia/laravel-ops-settings'))
        ->toHaveCount(2)
        ->and(
            collect(app(PermissionRegistry::class)->forPackage('yezzmedia/laravel-ops-settings'))
                ->pluck('name')->all()
        )->toBe(['ops.settings.view', 'ops.settings.manage'])
        ->and(app(FeatureRegistry::class)->forPackage('yezzmedia/laravel-ops-settings'))
        ->toHaveCount(6)
        ->and(app(OpsModuleRegistry::class)->forPackage('yezzmedia/laravel-ops-settings'))
        ->toHaveCount(6)
        ->and($doctorResults->keys()->all())->toContain('audit_configured', 'settings_completeness', 'settings_consistency', 'settings_store_ready')
        ->and(app(OpsSettingsManager::class))->toBeInstanceOf(OpsSettingsManager::class)
        ->and(app(UpdateOpsSettingsAction::class))->toBeInstanceOf(UpdateOpsSettingsAction::class);
});

it('merges the package configuration', function (): void {
    expect(config('ops-settings.cache.enabled'))->toBeFalse() // forced off in test env
        ->and(config('ops-settings.cache.store'))->toBeNull()
        ->and(config('ops-settings.audit.driver'))->toBeNull()
        ->and(config('ops-settings.defaults.seed_on_install'))->toBeTrue()
        ->and(config('ops-settings.workspace.history_limit'))->toBe(20)
        ->and(config('ops-settings.workspace.presets'))->toBe(['de', 'ch', 'at', 'us']);
});

it('registers a publishable ops settings config file', function (): void {
    $publishableConfigs = OpsSettingsServiceProvider::pathsToPublish(
        OpsSettingsServiceProvider::class,
        'ops-settings-config',
    );

    expect($publishableConfigs)->toHaveCount(1)
        ->and(array_keys($publishableConfigs)[0])->toEndWith('/config/ops-settings.php')
        ->and(array_values($publishableConfigs))->toBe([
            config_path('ops-settings.php'),
        ]);
});

it('describes the approved bootstrap surface', function (): void {
    $package = new OpsSettingsPlatformPackage;
    $metadata = $package->metadata();
    $permissions = collect($package->permissionDefinitions())->keyBy('name');
    $features = collect($package->featureDefinitions())->keyBy('name');
    $opsModules = collect($package->opsModuleDefinitions())->keyBy('key');
    $auditEvents = collect($package->auditEventDefinitions())->keyBy('key');

    expect($package)->toBeInstanceOf(PlatformPackage::class)
        ->and($package)->toBeInstanceOf(DefinesPermissions::class)
        ->and($package)->toBeInstanceOf(DefinesAuditEvents::class)
        ->and($package)->toBeInstanceOf(DefinesInstallSteps::class)
        ->and($package)->toBeInstanceOf(ProvidesDoctorChecks::class)
        ->and($package)->toBeInstanceOf(ProvidesOpsModules::class)
        ->and($package)->toBeInstanceOf(RegistersFeatures::class)
        ->and($metadata->name)->toBe('yezzmedia/laravel-ops-settings')
        ->and($metadata->vendor)->toBe('yezzmedia')
        ->and($metadata->packageClass)->toBe(OpsSettingsPlatformPackage::class)
        ->and($permissions->keys()->all())->toBe(['ops.settings.view', 'ops.settings.manage'])
        ->and($features->keys()->all())->toBe([
            'settings.identity',
            'settings.contact',
            'settings.brand',
            'settings.social',
            'settings.legal',
            'settings.website_defaults',
        ])
        ->and($package->installSteps())->toHaveCount(4)
        ->and($package->installSteps()[0])->toBeInstanceOf(PublishOpsSettingsMigrationsInstallStep::class)
        ->and($package->installSteps()[1])->toBeInstanceOf(ConfigureOpsSettingsAuditInstallStep::class)
        ->and($package->installSteps()[2])->toBeInstanceOf(EnsureOpsSettingsStoreReadyInstallStep::class)
        ->and($package->installSteps()[3])->toBeInstanceOf(SeedOpsSettingsDefaultsInstallStep::class)
        ->and($package->doctorChecks())->toHaveCount(4)
        ->and($package->doctorChecks()[0])->toBeInstanceOf(OpsSettingsAuditConfiguredCheck::class)
        ->and($package->doctorChecks()[1])->toBeInstanceOf(OpsSettingsCompletenessCheck::class)
        ->and($package->doctorChecks()[2])->toBeInstanceOf(OpsSettingsConsistencyCheck::class)
        ->and($package->doctorChecks()[3])->toBeInstanceOf(OpsSettingsStoreReadyCheck::class)
        ->and($opsModules->keys()->all())->toBe([
            'content.settings.identity',
            'content.settings.contact',
            'content.settings.brand',
            'content.settings.social',
            'content.settings.legal',
            'content.settings.website_defaults',
        ])
        ->and($opsModules->pluck('permissionHint')->unique()->values()->all())->toBe(['ops.settings.view'])
        ->and($auditEvents->keys()->all())->toBe(['ops.settings.updated'])
        ->and($auditEvents->get('ops.settings.updated')?->contextKeys)->toBe([
            'group', 'changed_keys', 'actor_id', 'old_values', 'new_values', 'context', 'source',
        ])
        ->and($auditEvents->get('ops.settings.updated')?->action)->toBe('updated')
        ->and($auditEvents->get('ops.settings.updated')?->subjectType)->toBe('ops_settings')
        ->and($auditEvents->get('ops.settings.updated')?->severity)->toBe('warning');
});
