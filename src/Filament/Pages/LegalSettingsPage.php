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
                ->description('Use the formal legal data that public-facing and compliance-related platform surfaces can rely on.')
                ->schema([
                    TextInput::make('legal_entity_name')
                        ->label('Legal Entity Name')
                        ->placeholder('Yezz Media GmbH')
                        ->helperText('Store the formal registered legal entity name exactly as it should appear in legal and compliance contexts.')
                        ->maxLength(255),
                    TextInput::make('registration_number')
                        ->label('Registration Number')
                        ->placeholder('HRB 123456')
                        ->helperText('Use the official commercial or registry number in the format expected by your jurisdiction.')
                        ->maxLength(100),
                    TextInput::make('vat_id')
                        ->label('VAT ID')
                        ->placeholder('DE123456789')
                        ->helperText('Store the tax or VAT identifier in the exact format used for invoices and legal notices.')
                        ->maxLength(50),
                    TextInput::make('privacy_contact_email')
                        ->label('Privacy Contact Email')
                        ->placeholder('privacy@example.com')
                        ->helperText('Use a monitored contact point for privacy and regulatory requests.')
                        ->email()
                        ->maxLength(255),
                ]),
            Section::make('Legal Notice')
                ->description('Use concise legal notice copy that can be reused safely before package-specific expansion is needed.')
                ->schema([
                    Textarea::make('legal_notice_snippet')
                        ->label('Legal Notice Snippet')
                        ->placeholder('Managing directors, registered office, and other short mandatory disclosure notes.')
                        ->helperText('Prefer concise, reusable notice text that remains valid across multiple pages and package integrations.')
                        ->rows(5),
                ]),
        ];
    }

    protected function getPageIntro(): string
    {
        return 'Legal settings hold the shared legal identity of the platform. Operators should keep these values accurate, auditable, and aligned with the current entity behind the service.';
    }

    protected function getPageHighlights(): array
    {
        return [
            'formal legal identity used across notices and compliance surfaces',
            'registration and tax references needed for trust and billing contexts',
            'privacy contact details and reusable legal notice copy',
        ];
    }

    protected function getPageExample(): string
    {
        return 'Example: use the exact registered entity name, current registration and VAT identifiers, and one monitored privacy inbox so legal surfaces stay consistent and reviewable.';
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
