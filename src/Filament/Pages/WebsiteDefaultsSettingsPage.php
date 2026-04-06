<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

class WebsiteDefaultsSettingsPage extends OpsSettingsGroupPage
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $title = 'Website Default Settings';

    protected static ?int $navigationSort = 60;

    protected static ?string $slug = 'ops-settings-website-defaults';

    protected function getGroup(): OpsSettingsGroup
    {
        return OpsSettingsGroup::WebsiteDefaults;
    }

    protected function getGroupSchema(): array
    {
        return [
            Section::make('Website Defaults')
                ->description('Define reusable fallback copy patterns that multiple websites or package-owned pages can share.')
                ->schema([
                    TextInput::make('default_site_title_pattern')
                        ->label('Default Site Title Pattern')
                        ->placeholder('%s | Yezz Platform')
                        ->helperText('Use a reusable title pattern where downstream pages can inject their own page title into a stable suffix or prefix.')
                        ->maxLength(255),
                    TextInput::make('default_footer_label')
                        ->label('Default Footer Label')
                        ->placeholder('Operated by Yezz Media')
                        ->helperText('Use a short footer phrase that can appear across package-owned websites without needing local overrides.')
                        ->maxLength(255),
                    TextInput::make('default_support_label')
                        ->label('Default Support Label')
                        ->placeholder('Need help? Contact our support team.')
                        ->helperText('Use a neutral support label that can fit help sections, contact cards, or fallback support blocks.')
                        ->maxLength(255),
                ]),
        ];
    }

    protected function getPageIntro(): string
    {
        return 'Website defaults provide reusable text patterns for page titles, footer copy, and support wording. Use them when a package needs a sensible fallback instead of inventing its own baseline.';
    }

    protected function getPageHighlights(): array
    {
        return [
            'fallback title patterns for downstream websites and package pages',
            'shared footer wording for multi-surface consistency',
            'reusable support copy that keeps operator-facing language aligned',
        ];
    }

    protected function getPageExample(): string
    {
        return 'Example: define a title pattern like `%s | Yezz Platform`, a concise footer owner label, and one calm support sentence that works in multiple layouts.';
    }

    protected function loadCurrentData(): array
    {
        $settings = app(OpsSettingsManager::class)->websiteDefaults();

        return [
            'default_site_title_pattern' => $settings->default_site_title_pattern,
            'default_footer_label' => $settings->default_footer_label,
            'default_support_label' => $settings->default_support_label,
        ];
    }
}
