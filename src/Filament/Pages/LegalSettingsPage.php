<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

class LegalSettingsPage extends OpsSettingsGroupPage
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $title = 'Legal Settings';

    protected static ?int $navigationSort = 50;

    protected static ?string $slug = 'ops-settings-legal';

    protected function getGroup(): OpsSettingsGroup
    {
        return OpsSettingsGroup::Legal;
    }

    protected function getGroupSchema(): array
    {
        return [
            Section::make('Legal Entity')
                ->schema([
                    TextInput::make('legal_entity_name')
                        ->label('Legal Entity Name')
                        ->maxLength(255),
                    TextInput::make('registration_number')
                        ->label('Registration Number')
                        ->maxLength(100),
                    TextInput::make('vat_id')
                        ->label('VAT ID')
                        ->maxLength(50),
                    TextInput::make('privacy_contact_email')
                        ->label('Privacy Contact Email')
                        ->email()
                        ->maxLength(255),
                ]),
            Section::make('Legal Notice')
                ->schema([
                    Textarea::make('legal_notice_snippet')
                        ->label('Legal Notice Snippet')
                        ->rows(5),
                ]),
        ];
    }

    protected function loadCurrentData(): array
    {
        $settings = app(OpsSettingsManager::class)->legal();

        return [
            'legal_entity_name' => $settings->legal_entity_name,
            'registration_number' => $settings->registration_number,
            'vat_id' => $settings->vat_id,
            'legal_notice_snippet' => $settings->legal_notice_snippet,
            'privacy_contact_email' => $settings->privacy_contact_email,
        ];
    }
}
