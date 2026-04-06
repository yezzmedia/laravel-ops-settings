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
                ->description('Capture the copy and assets that should define the shared platform brand wherever no package-specific override exists.')
                ->schema([
                    TextInput::make('brand_name')
                        ->label('Brand Name')
                        ->placeholder('Yezz Platform')
                        ->helperText('Use the short, presentation-ready brand name that should appear in headers, titles, and product copy.')
                        ->maxLength(255),
                    TextInput::make('brand_tagline')
                        ->label('Brand Tagline')
                        ->placeholder('Operations made visible')
                        ->helperText('Optional concise statement that communicates tone and positioning without becoming campaign-specific.')
                        ->maxLength(500),
                    TextInput::make('logo_reference')
                        ->label('Logo Reference')
                        ->placeholder('brand/primary-lockup')
                        ->helperText('Store a stable internal asset reference or identifier rather than a one-off local filename.')
                        ->maxLength(500),
                ]),
            Section::make('Brand Colors')
                ->description('Use colors that can safely become reusable defaults across package-owned surfaces.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('primary_color')
                                ->label('Primary Color')
                                ->placeholder('#1F3A5F')
                                ->helperText('Use a hex value that represents the main platform color and is safe for repeated reuse.')
                                ->maxLength(20),
                            TextInput::make('secondary_color')
                                ->label('Secondary Color')
                                ->placeholder('#9F7AEA')
                                ->helperText('Use a supporting hex value that complements the primary brand color.')
                                ->maxLength(20),
                        ]),
                ]),
        ];
    }

    protected function getPageIntro(): string
    {
        return 'Brand settings provide the reusable naming, copy, and visual defaults that help multiple package surfaces feel like one coherent platform.';
    }

    protected function getPageHighlights(): array
    {
        return [
            'shared brand naming and tagline defaults',
            'palette references that support consistent UI styling',
            'stable asset references for downstream design systems and pages',
        ];
    }

    protected function getPageExample(): string
    {
        return 'Example: define one polished brand name, one timeless tagline, and two reusable brand colors so package UIs can stay aligned without inventing their own palette.';
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
