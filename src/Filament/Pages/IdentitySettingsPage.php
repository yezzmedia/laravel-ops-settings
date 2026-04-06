<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

class IdentitySettingsPage extends OpsSettingsGroupPage
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-identification';

    protected static ?string $title = 'Identity Settings';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'ops-settings-identity';

    protected function getGroup(): OpsSettingsGroup
    {
        return OpsSettingsGroup::Identity;
    }

    protected function getGroupSchema(): array
    {
        return [
            Section::make('Operator Identity')
                ->schema([
                    TextInput::make('name')
                        ->label('Operator Name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('platform_label')
                        ->label('Platform Label')
                        ->maxLength(255),
                ]),
        ];
    }

    protected function loadCurrentData(): array
    {
        $settings = app(OpsSettingsManager::class)->identity();

        return [
            'name' => $settings->name,
            'platform_label' => $settings->platform_label,
        ];
    }
}
