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
                ->description('Keep the primary support and escalation contacts here so packages do not invent their own variations.')
                ->schema([
                    TextInput::make('support_email')
                        ->label('Support Email')
                        ->placeholder('support@example.com')
                        ->helperText('Use the main monitored support inbox that public pages and operator workflows should reuse.')
                        ->email()
                        ->maxLength(255),
                    TextInput::make('contact_phone')
                        ->label('Contact Phone')
                        ->placeholder('+49 30 1234 5678')
                        ->helperText('Prefer one internationally readable contact number with a stable country code format.')
                        ->tel()
                        ->maxLength(50),
                ]),
            Section::make('Address')
                ->description('Use the postal details that should appear on legal, support, and trust-building surfaces.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('address_line_1')
                                ->label('Address Line 1')
                                ->placeholder('Example Street 12')
                                ->helperText('Street and house number or the clearest first postal line.')
                                ->maxLength(255),
                            TextInput::make('address_line_2')
                                ->label('Address Line 2')
                                ->placeholder('2nd floor, Office 4')
                                ->helperText('Optional additional routing details such as building, floor, or mailbox.')
                                ->maxLength(255),
                            TextInput::make('postal_code')
                                ->label('Postal Code')
                                ->placeholder('10115')
                                ->helperText('Store the postal code in the standard format used in the destination country.')
                                ->maxLength(20),
                            TextInput::make('city')
                                ->label('City')
                                ->placeholder('Berlin')
                                ->helperText('Use the full city name as it should appear in customer-facing contexts.')
                                ->maxLength(100),
                            TextInput::make('country_code')
                                ->label('Country Code')
                                ->placeholder('DE')
                                ->helperText('Use the ISO 3166-1 alpha-2 code, for example `DE`, `FR`, or `US`.')
                                ->maxLength(2),
                        ]),
                ]),
        ];
    }

    protected function getPageIntro(): string
    {
        return 'Contact settings define the reusable support and postal information for the platform. Use this page to keep customer-facing contact details accurate and easy to maintain.';
    }

    protected function getPageHighlights(): array
    {
        return [
            'support channels used across public and operator-facing experiences',
            'postal address data for trust, legal, and support surfaces',
            'consistent contact details that downstream teams can reuse without editing',
        ];
    }

    protected function getPageExample(): string
    {
        return 'Example: point support email to a monitored shared inbox, store one internationally readable phone number, and keep the legal/postal address aligned with your published legal entity details.';
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
