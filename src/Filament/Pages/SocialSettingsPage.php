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
                ->schema([
                    TextInput::make('facebook_url')
                        ->label('Facebook URL')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('instagram_url')
                        ->label('Instagram URL')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('linkedin_url')
                        ->label('LinkedIn URL')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('x_url')
                        ->label('X (Twitter) URL')
                        ->url()
                        ->maxLength(500),
                    TextInput::make('youtube_url')
                        ->label('YouTube URL')
                        ->url()
                        ->maxLength(500),
                ]),
        ];
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
