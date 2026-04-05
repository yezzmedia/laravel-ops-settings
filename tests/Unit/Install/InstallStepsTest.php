<?php

declare(strict_types=1);

use Tests\Fixtures\FakeOpsSettingsStoreSetup;
use YezzMedia\Foundation\Data\InstallContext;
use YezzMedia\OpsSettings\Install\EnsureOpsSettingsStoreReadyInstallStep;
use YezzMedia\OpsSettings\Install\PublishOpsSettingsMigrationsInstallStep;
use YezzMedia\OpsSettings\Install\SeedOpsSettingsDefaultsInstallStep;
use YezzMedia\OpsSettings\Support\OpsSettingsStoreSetup;

it('publishes migrations when they are missing', function (): void {
    $setup = new FakeOpsSettingsStoreSetup(hasMigrationsPublished: false);
    $step = new PublishOpsSettingsMigrationsInstallStep($setup);

    expect($step->shouldRun(new InstallContext))->toBeTrue();

    $step->handle(new InstallContext);

    expect($setup->calls)->toBe(['publish_migrations'])
        ->and($setup->migrationsWereForced)->toBeFalse();
});

it('skips migration publishing when migrations are already published', function (): void {
    $setup = new FakeOpsSettingsStoreSetup(hasMigrationsPublished: true);
    $step = new PublishOpsSettingsMigrationsInstallStep($setup);

    expect($step->shouldRun(new InstallContext))->toBeFalse();
});

it('re-publishes migrations during refresh mode', function (): void {
    $setup = new FakeOpsSettingsStoreSetup(hasMigrationsPublished: true);
    $step = new PublishOpsSettingsMigrationsInstallStep($setup);

    expect($step->shouldRun(new InstallContext(refreshPublishedResources: true)))->toBeTrue();

    $step->handle(new InstallContext(refreshPublishedResources: true));

    expect($setup->migrationsWereForced)->toBeTrue();
});

it('requires explicit migration permission before ensuring store readiness', function (): void {
    $setup = new FakeOpsSettingsStoreSetup(hasReadyStore: false);
    $step = new EnsureOpsSettingsStoreReadyInstallStep($setup);

    expect(fn () => $step->handle(new InstallContext))
        ->toThrow(RuntimeException::class, 'The ops settings store is not ready');
});

it('runs migrations when store is not ready and allowMigrations is true', function (): void {
    $setup = new FakeOpsSettingsStoreSetup(hasReadyStore: false);
    $step = new EnsureOpsSettingsStoreReadyInstallStep($setup);

    $step->handle(new InstallContext(allowMigrations: true));

    expect($setup->calls)->toBe(['run_migrations'])
        ->and($setup->hasReadyStore)->toBeTrue();
});

it('skips ensure step when store is already ready and no pending migrations', function (): void {
    $setup = new FakeOpsSettingsStoreSetup(hasReadyStore: true, hasPendingMigrations: false);
    $step = new EnsureOpsSettingsStoreReadyInstallStep($setup);

    expect($step->shouldRun(new InstallContext))->toBeFalse();
});

it('seeds defaults only when store is ready and seed_on_install is enabled', function (): void {
    $setup = new FakeOpsSettingsStoreSetup(hasReadyStore: true);
    app()->instance(OpsSettingsStoreSetup::class, $setup);
    $step = new SeedOpsSettingsDefaultsInstallStep($setup);

    config()->set('ops-settings.defaults.seed_on_install', true);

    expect($step->shouldRun(new InstallContext))->toBeTrue();
});

it('skips seeding when seed_on_install is disabled', function (): void {
    $setup = new FakeOpsSettingsStoreSetup(hasReadyStore: true);
    $step = new SeedOpsSettingsDefaultsInstallStep($setup);

    config()->set('ops-settings.defaults.seed_on_install', false);

    expect($step->shouldRun(new InstallContext))->toBeFalse();
});

it('skips seeding when store is not ready', function (): void {
    $setup = new FakeOpsSettingsStoreSetup(hasReadyStore: false);
    $step = new SeedOpsSettingsDefaultsInstallStep($setup);

    config()->set('ops-settings.defaults.seed_on_install', true);

    expect($step->shouldRun(new InstallContext))->toBeFalse();
});
