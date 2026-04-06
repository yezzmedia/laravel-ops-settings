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
                ->schema([
                    TextInput::make('default_site_title_pattern')
                        ->label('Default Site Title Pattern')
                        ->maxLength(255),
                    TextInput::make('default_footer_label')
                        ->label('Default Footer Label')
                        ->maxLength(255),
                    TextInput::make('default_support_label')
                        ->label('Default Support Label')
                        ->maxLength(255),
                ]),
        ];
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
