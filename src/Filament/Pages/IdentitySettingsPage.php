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
                ->description('Define the core operator name and optional platform label that other packages should treat as the canonical naming source.')
                ->schema([
                    TextInput::make('name')
                        ->label('Operator Name')
                        ->placeholder('Yezz Media')
                        ->helperText('Use the stable public-facing operator or company name that should appear across shared platform surfaces.')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('platform_label')
                        ->label('Platform Label')
                        ->placeholder('Operations Cloud')
                        ->helperText('Optional sub-brand or environment label for cases where the operator name needs a second clarifying layer.')
                        ->maxLength(255),
                ]),
        ];
    }

    protected function getPageIntro(): string
    {
        return 'Identity settings establish the naming baseline for the wider platform. Downstream packages should be able to reuse these values without reinterpreting what the operator is called.';
    }

    protected function getPageHighlights(): array
    {
        return [
            'operator naming shown in shared admin and ops experiences',
            'platform labels used when the main brand needs clarification',
            'stable values that other teams can reference consistently',
        ];
    }

    protected function getPageExample(): string
    {
        return 'Example: use `Yezz Media` as the operator name and `Platform Operations` as the platform label when you want one formal owner plus one clarifying operational label.';
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
