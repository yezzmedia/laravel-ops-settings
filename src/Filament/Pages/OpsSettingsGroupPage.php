<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Gate;
use UnitEnum;
use YezzMedia\OpsSettings\Actions\UpdateOpsSettingsAction;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;

abstract class OpsSettingsGroupPage extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public array $data = [];

    abstract protected function getGroup(): OpsSettingsGroup;

    abstract protected function getGroupSchema(): array;

    abstract protected function loadCurrentData(): array;

    public static function canAccess(): bool
    {
        return Gate::check('ops.settings.manage');
    }

    public function mount(): void
    {
        if (! static::canAccess()) {
            abort(403);
        }

        $this->data = $this->loadCurrentData();
    }

    public function save(): void
    {
        app(UpdateOpsSettingsAction::class)->execute(
            $this->getGroup(),
            $this->data,
        );

        Notification::make()
            ->title('Settings saved successfully.')
            ->success()
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make($this->getGroupSchema())
                ->id('settings-form')
                ->statePath('data')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save Settings')
                            ->submit('settings-form'),
                        Action::make('back')
                            ->label('Back to Settings')
                            ->url(OpsSettingsPage::getUrl(panel: (string) config('ops.panel.id', 'ops')))
                            ->color('gray'),
                    ]),
                ]),
        ]);
    }
}
