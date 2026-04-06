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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use UnitEnum;
use YezzMedia\OpsSettings\Actions\UpdateOpsSettingsAction;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;
use YezzMedia\OpsSettings\Support\OpsSettingsPageSchema;

class OpsSettingsPage extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Platform Settings';

    protected static ?int $navigationSort = 50;

    protected static ?string $title = 'Platform Settings';

    protected static ?string $slug = 'ops-settings';

    public static function canAccess(): bool
    {
        return Gate::check('ops.settings.view');
    }

    public array $data = [];

    public function mount(): void
    {
        if (! static::canAccess()) {
            abort(403);
        }

        $manager = app(OpsSettingsManager::class);

        foreach (OpsSettingsGroup::cases() as $group) {
            $this->data[$group->value] = OpsSettingsPageSchema::currentData($manager, $group);
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Platform Settings')
                ->description('Manage operator-facing defaults, communication channels, legal content, and reusable website fallbacks from one tabbed workspace. Each group keeps its own save boundary so audit and change tracking stay explicit.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Section::make('Readiness Snapshot')
                        ->description('Use these helpers to see whether the platform defaults are complete enough for downstream reuse.')
                        ->schema([
                            Grid::make(2)
                                ->schema([
                                    Section::make('Current completion state')
                                        ->description(app(OpsSettingsManager::class)->isComplete()
                                            ? 'All required baseline fields are currently filled.'
                                            : 'Some required baseline fields are still missing and should be completed before wider package reuse.')
                                        ->schema([]),
                                    Section::make('Missing required fields')
                                        ->description($this->missingRequiredFieldsDescription())
                                        ->schema([]),
                                ]),
                        ]),
                    Tabs::make('Settings Tabs')
                        ->tabs($this->settingsTabs())
                        ->vertical()
                        ->persistTabInQueryString('tab')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function saveIdentity(): void
    {
        $this->saveGroup(OpsSettingsGroup::Identity);
    }

    public function saveContact(): void
    {
        $this->saveGroup(OpsSettingsGroup::Contact);
    }

    public function saveBrand(): void
    {
        $this->saveGroup(OpsSettingsGroup::Brand);
    }

    public function saveSocial(): void
    {
        $this->saveGroup(OpsSettingsGroup::Social);
    }

    public function saveLegal(): void
    {
        $this->saveGroup(OpsSettingsGroup::Legal);
    }

    public function saveWebsiteDefaults(): void
    {
        $this->saveGroup(OpsSettingsGroup::WebsiteDefaults);
    }

    /**
     * @return array<string, Tab>
     */
    private function settingsTabs(): array
    {
        $tabs = [];

        foreach (OpsSettingsGroup::cases() as $group) {
            $tabs[$group->value] = Tab::make($group->label())
                ->icon($group->icon())
                ->schema([
                    Section::make($group->label())
                        ->description(OpsSettingsPageSchema::intro($group))
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Section::make('What this controls')
                                        ->description('Use these defaults anywhere the platform needs one shared operator-owned source of truth.')
                                        ->schema([
                                            Text::make(new HtmlString((string) str(implode("\n", array_map(
                                                static fn (string $highlight): string => "- {$highlight}",
                                                OpsSettingsPageSchema::highlights($group),
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
                                            Text::make(OpsSettingsPageSchema::example($group)),
                                        ]),
                                ]),
                            Group::make([
                                Form::make(OpsSettingsPageSchema::schema($group))
                                    ->id($this->formId($group))
                                    ->statePath('data.'.$group->value)
                                    ->livewireSubmitHandler($this->submitHandler($group))
                                    ->footer([
                                        Actions::make([
                                            Action::make('save_'.$group->value)
                                                ->label('Save '.$group->label())
                                                ->submit($this->formId($group))
                                                ->visible(Gate::check('ops.settings.manage')),
                                        ]),
                                    ]),
                            ])->columnSpanFull(),
                        ]),
                ]);
        }

        return $tabs;
    }

    private function saveGroup(OpsSettingsGroup $group): void
    {
        if (Gate::denies('ops.settings.manage')) {
            abort(403);
        }

        app(UpdateOpsSettingsAction::class)->execute(
            $group,
            $this->data[$group->value] ?? [],
            actorId: Auth::id(),
            context: [
                'request_id' => (string) Str::uuid(),
                'panel_id' => (string) config('ops.panel.id', 'ops'),
            ],
            source: 'ops_panel',
        );

        Notification::make()
            ->title($group->label().' settings saved successfully.')
            ->success()
            ->send();
    }

    private function formId(OpsSettingsGroup $group): string
    {
        return 'ops-settings-'.$group->value.'-form';
    }

    private function submitHandler(OpsSettingsGroup $group): string
    {
        return match ($group) {
            OpsSettingsGroup::Identity => 'saveIdentity',
            OpsSettingsGroup::Contact => 'saveContact',
            OpsSettingsGroup::Brand => 'saveBrand',
            OpsSettingsGroup::Social => 'saveSocial',
            OpsSettingsGroup::Legal => 'saveLegal',
            OpsSettingsGroup::WebsiteDefaults => 'saveWebsiteDefaults',
        };
    }

    private function missingRequiredFieldsDescription(): string
    {
        $missing = app(OpsSettingsManager::class)->missingRequiredFields();

        if ($missing === []) {
            return 'No required baseline fields are currently missing.';
        }

        return collect($missing)
            ->map(fn (array $fields, string $group): string => sprintf(
                '%s: %s',
                OpsSettingsGroup::fromValue($group)->label(),
                implode(', ', $fields),
            ))
            ->implode(' | ');
    }
}
