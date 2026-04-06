<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

class SocialSettingsPage extends OpsSettingsGroupPage
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-share';

    protected static ?string $title = 'Social Settings';

    protected static ?int $navigationSort = 40;

    protected static ?string $slug = 'ops-settings-social';

    protected function getGroup(): OpsSettingsGroup
    {
        return OpsSettingsGroup::Social;
    }

    protected function getGroupSchema(): array
    {
        return [
            Section::make('Social Media Links')
                ->description('Keep only official public profiles here so downstream pages can expose them confidently.')
                ->schema([
                    TextInput::make('facebook_url')
                        ->label('Facebook URL')
                        ->placeholder('https://www.facebook.com/your-brand')
                        ->helperText('Use the full canonical profile URL, including protocol, rather than a partial handle.')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('instagram_url')
                        ->label('Instagram URL')
                        ->placeholder('https://www.instagram.com/your-brand')
                        ->helperText('Store the public profile URL that should be linked from websites or profile sections.')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('linkedin_url')
                        ->label('LinkedIn URL')
                        ->placeholder('https://www.linkedin.com/company/your-brand')
                        ->helperText('Prefer the company or organization page URL, not a personal profile, unless that is intentional.')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('x_url')
                        ->label('X (Twitter) URL')
                        ->placeholder('https://x.com/your-brand')
                        ->helperText('Use the canonical public profile URL so links remain stable if the platform is reused elsewhere.')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('youtube_url')
                        ->label('YouTube URL')
                        ->placeholder('https://www.youtube.com/@your-brand')
                        ->helperText('Store the channel or handle URL that should represent the official video presence.')
                        ->url()
                        ->maxLength(500),
                ]),
        ];
    }

    protected function getPageIntro(): string
    {
        return 'Social settings centralize the official public profiles for the platform. These values should be stable enough to embed in multiple footers, profile blocks, and operator-maintained pages.';
    }

    protected function getPageHighlights(): array
    {
        return [
            'official platform-owned social destinations',
            'consistent external links for websites and support surfaces',
            'one reusable source of truth for public profile URLs',
        ];
    }

    protected function getPageExample(): string
    {
        return 'Example: keep only the official branded profiles here, and leave temporary campaign accounts or experimental channels out of the shared defaults.';
    }

    protected function loadCurrentData(): array
    {
        $settings = app(OpsSettingsManager::class)->social();

        return [
            'facebook_url' => $settings->facebook_url,
            'instagram_url' => $settings->instagram_url,
            'linkedin_url' => $settings->linkedin_url,
            'x_url' => $settings->x_url,
            'youtube_url' => $settings->youtube_url,
        ];
    }
}
