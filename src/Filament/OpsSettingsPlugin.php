<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use YezzMedia\OpsSettings\Filament\Pages\BrandSettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\ContactSettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\IdentitySettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\LegalSettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\OpsSettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\SocialSettingsPage;
use YezzMedia\OpsSettings\Filament\Pages\WebsiteDefaultsSettingsPage;

/**
 * Registers the Ops Settings UI pages into a Filament panel.
 */
final class OpsSettingsPlugin implements Plugin
{
    public static function make(): static
    {
        return new self;
    }

    public function getId(): string
    {
        return 'ops-settings';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            OpsSettingsPage::class,
            IdentitySettingsPage::class,
            ContactSettingsPage::class,
            BrandSettingsPage::class,
            SocialSettingsPage::class,
            LegalSettingsPage::class,
            WebsiteDefaultsSettingsPage::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
