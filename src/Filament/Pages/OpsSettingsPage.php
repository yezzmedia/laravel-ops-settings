<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use UnitEnum;

class OpsSettingsPage extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 50;

    protected static ?string $title = 'Platform Settings';

    protected static ?string $slug = 'ops-settings';

    public static function canAccess(): bool
    {
        return Gate::check('ops.settings.view');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Platform Settings')
                ->description('Manage the operator-facing defaults, communication channels, legal content, and reusable website defaults from one dedicated settings area. Each section now includes practical guidance so operators can keep settings consistent and production-ready.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Group::make([
                        Actions::make([
                            Action::make('identity')
                                ->label('Identity')
                                ->icon('heroicon-o-identification')
                                ->url(IdentitySettingsPage::getUrl(panel: (string) config('ops.panel.id', 'ops'))),
                            Action::make('contact')
                                ->label('Contact')
                                ->icon('heroicon-o-phone')
                                ->url(ContactSettingsPage::getUrl(panel: (string) config('ops.panel.id', 'ops'))),
                            Action::make('brand')
                                ->label('Brand')
                                ->icon('heroicon-o-paint-brush')
                                ->url(BrandSettingsPage::getUrl(panel: (string) config('ops.panel.id', 'ops'))),
                            Action::make('social')
                                ->label('Social')
                                ->icon('heroicon-o-share')
                                ->url(SocialSettingsPage::getUrl(panel: (string) config('ops.panel.id', 'ops'))),
                            Action::make('legal')
                                ->label('Legal')
                                ->icon('heroicon-o-document-text')
                                ->url(LegalSettingsPage::getUrl(panel: (string) config('ops.panel.id', 'ops'))),
                            Action::make('website_defaults')
                                ->label('Website Defaults')
                                ->icon('heroicon-o-globe-alt')
                                ->url(WebsiteDefaultsSettingsPage::getUrl(panel: (string) config('ops.panel.id', 'ops'))),
                        ])->fullWidth(),
                    ])->columnSpanFull(),
                    Section::make('How to use this area')
                        ->description('Treat these settings as stable platform defaults. They should support multiple packages, survive visual redesigns, and stay understandable for other operators.')
                        ->schema([
                            Section::make('Recommended workflow')
                                ->description('Work from broad platform identity down to channel-specific details.')
                                ->schema([]),
                            Section::make('Good defaults are')
                                ->description('Clear, reusable, legally accurate, and not tied to one temporary campaign or one-off page.')
                                ->schema([]),
                            Section::make('Before saving')
                                ->description('Check formatting, brand consistency, public-facing wording, and whether downstream teams can reuse the values safely.')
                                ->schema([]),
                        ]),
                    Grid::make(3)->schema([
                        Section::make('Identity')
                            ->description('Operator naming and platform labels used across dashboards, generated copy, and platform-facing references.'),
                        Section::make('Brand')
                            ->description('Brand copy, palette defaults, and reusable references that help packages stay visually aligned.'),
                        Section::make('Website Defaults')
                            ->description('Reusable titles, footer labels, and support wording that should work consistently across multiple surfaces.'),
                        Section::make('Contact')
                            ->description('Support and postal contact details that operators, pages, and legal surfaces can safely reuse.'),
                        Section::make('Social')
                            ->description('Official public social channels that downstream pages can expose without guessing profile URLs.'),
                        Section::make('Legal')
                            ->description('Legal identity, registrations, and notice copy that must remain accurate and easy to audit.'),
                    ]),
                ]),
        ]);
    }
}
