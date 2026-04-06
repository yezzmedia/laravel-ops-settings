<?php

declare(strict_types=1);

use Filament\Panel;
use Illuminate\Support\Facades\Gate;
use YezzMedia\OpsSettings\Filament\OpsSettingsPlugin;
use YezzMedia\OpsSettings\Filament\Pages\BrandSettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\ContactSettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\IdentitySettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\LegalSettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\OpsSettingsGroupPage;
use YezzMedia\OpsSettings\Filament\Pages\OpsSettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\SocialSettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\WebsiteDefaultsSettingsPage;

it('resolves a plugin ID of ops-settings', function (): void {
    $plugin = OpsSettingsPlugin::make();

    expect($plugin->getId())->toBe('ops-settings');
});

it('creates an instance via the static make method', function (): void {
    expect(OpsSettingsPlugin::make())->toBeInstanceOf(OpsSettingsPlugin::class);
});

it('OpsSettingsPage checks ops.settings.view gate for access', function (): void {
    Gate::define('ops.settings.view', fn ($user = null) => false);

    expect(OpsSettingsPage::canAccess())->toBeFalse();

    Gate::define('ops.settings.view', fn ($user = null) => true);

    expect(OpsSettingsPage::canAccess())->toBeTrue();
});

it('all group pages extend OpsSettingsGroupPage', function (): void {
    $pages = [
        IdentitySettingsPage::class,
        ContactSettingsPage::class,
        BrandSettingsPage::class,
        SocialSettingsPage::class,
        LegalSettingsPage::class,
        WebsiteDefaultsSettingsPage::class,
    ];

    foreach ($pages as $page) {
        expect(is_subclass_of($page, OpsSettingsGroupPage::class))->toBeTrue();
    }
});

it('OpsSettingsGroupPage checks ops.settings.manage gate for access', function (): void {
    Gate::define('ops.settings.manage', fn ($user = null) => false);

    expect(IdentitySettingsPage::canAccess())->toBeFalse();

    Gate::define('ops.settings.manage', fn ($user = null) => true);

    expect(IdentitySettingsPage::canAccess())->toBeTrue();
});

it('settings child pages register under the Settings parent navigation item', function (): void {
    $pages = [
        IdentitySettingsPage::class,
        ContactSettingsPage::class,
        BrandSettingsPage::class,
        SocialSettingsPage::class,
        LegalSettingsPage::class,
        WebsiteDefaultsSettingsPage::class,
    ];

    foreach ($pages as $page) {
        expect($page::getNavigationGroup())
            ->toBe('Settings', "Expected {$page} to register inside the Settings navigation group");
    }
});

it('the Settings hub page stays out of the sidebar while group pages form the Settings section', function (): void {
    expect(OpsSettingsPage::shouldRegisterNavigation())->toBeFalse()
        ->and(IdentitySettingsPage::getNavigationGroup())->toBe('Settings')
        ->and(ContactSettingsPage::getNavigationGroup())->toBe('Settings')
        ->and(BrandSettingsPage::getNavigationGroup())->toBe('Settings')
        ->and(SocialSettingsPage::getNavigationGroup())->toBe('Settings')
        ->and(LegalSettingsPage::getNavigationGroup())->toBe('Settings')
        ->and(WebsiteDefaultsSettingsPage::getNavigationGroup())->toBe('Settings');
});

it('plugin registers all 7 expected page classes', function (): void {
    $expectedPages = [
        OpsSettingsPage::class,
        IdentitySettingsPage::class,
        ContactSettingsPage::class,
        BrandSettingsPage::class,
        SocialSettingsPage::class,
        LegalSettingsPage::class,
        WebsiteDefaultsSettingsPage::class,
    ];

    $registeredPages = [];

    $panelMock = Mockery::mock(Panel::class);
    $panelMock->shouldReceive('pages')
        ->once()
        ->withArgs(function (array $pages) use (&$registeredPages): bool {
            $registeredPages = $pages;

            return true;
        })
        ->andReturnSelf();

    OpsSettingsPlugin::make()->register($panelMock);

    expect($registeredPages)->toBe($expectedPages);
});
