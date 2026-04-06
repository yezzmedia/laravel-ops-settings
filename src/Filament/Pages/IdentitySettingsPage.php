<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;
use YezzMedia\OpsSettings\Support\OpsSettingsPageSchema;

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
        return OpsSettingsPageSchema::schema($this->getGroup());
    }

    protected function getPageIntro(): string
    {
        return OpsSettingsPageSchema::intro($this->getGroup());
    }

    protected function getPageHighlights(): array
    {
        return OpsSettingsPageSchema::highlights($this->getGroup());
    }

    protected function getPageExample(): string
    {
        return OpsSettingsPageSchema::example($this->getGroup());
    }

    protected function loadCurrentData(): array
    {
        return OpsSettingsPageSchema::currentData(app(OpsSettingsManager::class), $this->getGroup());
    }
}
