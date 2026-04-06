<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
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

    abstract protected function getPageIntro(): string;

    /**
     * @return array<int, string>
     */
    abstract protected function getPageHighlights(): array;

    abstract protected function getPageExample(): string;

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
            actorId: Auth::id(),
            context: [
                'request_id' => (string) Str::uuid(),
                'panel_id' => (string) config('ops.panel.id', 'ops'),
            ],
            source: 'ops_panel',
        );

        Notification::make()
            ->title('Settings saved successfully.')
            ->success()
            ->send();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make((string) static::$title)
                ->description($this->getPageIntro())
                ->icon(static::$navigationIcon)
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Section::make('What this controls')
                                ->description('Use these defaults anywhere the platform needs one shared operator-owned source of truth.')
                                ->schema([
                                    Text::make(new HtmlString((string) str(implode("\n", array_map(
                                        static fn (string $highlight): string => "- {$highlight}",
                                        $this->getPageHighlights(),
                                    )))->inlineMarkdown())),
                                ]),
                            Section::make('Operator guidance')
                                ->description('Keep entries stable and production-ready so downstream packages can rely on them.')
                                ->schema([
                                    Text::make('Review values for consistency, keep naming predictable, and prefer reusable defaults over campaign-specific wording.'),
                                ]),
                            Section::make('Example usage')
                                ->description('Use this as a reference point for what a polished value set looks like.')
                                ->schema([
                                    Text::make($this->getPageExample()),
                                ]),
                        ]),
                    Group::make([
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
                    ])->columnSpanFull(),
                ]),
        ]);
    }
}
