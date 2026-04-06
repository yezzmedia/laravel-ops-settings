<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

class BrandSettingsPage extends OpsSettingsGroupPage
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $title = 'Brand Settings';

    protected static ?int $navigationSort = 30;

    protected static ?string $slug = 'ops-settings-brand';

    protected function getGroup(): OpsSettingsGroup
    {
        return OpsSettingsGroup::Brand;
    }

    protected function getGroupSchema(): array
    {
        return [
            Section::make('Brand Identity')
                ->schema([
                    TextInput::make('brand_name')
                        ->label('Brand Name')
                        ->maxLength(255),
                    TextInput::make('brand_tagline')
                        ->label('Brand Tagline')
                        ->maxLength(500),
                    TextInput::make('logo_reference')
                        ->label('Logo Reference')
                        ->maxLength(500),
                ]),
            Section::make('Brand Colors')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('primary_color')
                                ->label('Primary Color')
                                ->maxLength(20),
                            TextInput::make('secondary_color')
                                ->label('Secondary Color')
                                ->maxLength(20),
                        ]),
                ]),
        ];
    }

    protected function loadCurrentData(): array
    {
        $settings = app(OpsSettingsManager::class)->brand();

        return [
            'brand_name' => $settings->brand_name,
            'brand_tagline' => $settings->brand_tagline,
            'primary_color' => $settings->primary_color,
            'secondary_color' => $settings->secondary_color,
            'logo_reference' => $settings->logo_reference,
        ];
    }
}
