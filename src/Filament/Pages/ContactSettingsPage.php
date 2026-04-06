<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

class ContactSettingsPage extends OpsSettingsGroupPage
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-phone';

    protected static ?string $title = 'Contact Settings';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'ops-settings-contact';

    protected function getGroup(): OpsSettingsGroup
    {
        return OpsSettingsGroup::Contact;
    }

    protected function getGroupSchema(): array
    {
        return [
            Section::make('Contact Details')
                ->schema([
                    TextInput::make('support_email')
                        ->label('Support Email')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('contact_phone')
                        ->label('Contact Phone')
                        ->tel()
                        ->maxLength(50),
                ]),
            Section::make('Address')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('address_line_1')
                                ->label('Address Line 1')
                                ->maxLength(255),
                            TextInput::make('address_line_2')
                                ->label('Address Line 2')
                                ->maxLength(255),
                            TextInput::make('postal_code')
                                ->label('Postal Code')
                                ->maxLength(20),
                            TextInput::make('city')
                                ->label('City')
                                ->maxLength(100),
                            TextInput::make('country_code')
                                ->label('Country Code')
                                ->maxLength(2),
                        ]),
                ]),
        ];
    }

    protected function loadCurrentData(): array
    {
        $settings = app(OpsSettingsManager::class)->contact();

        return [
            'support_email' => $settings->support_email,
            'contact_phone' => $settings->contact_phone,
            'address_line_1' => $settings->address_line_1,
            'address_line_2' => $settings->address_line_2,
            'postal_code' => $settings->postal_code,
            'city' => $settings->city,
            'country_code' => $settings->country_code,
        ];
    }
}
