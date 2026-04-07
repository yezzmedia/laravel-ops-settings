<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use JsonException;
use UnitEnum;
use YezzMedia\OpsSettings\Actions\UpdateOpsSettingsAction;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;
use YezzMedia\OpsSettings\Support\OpsSettingsPageSchema;
use YezzMedia\OpsSettings\Support\OpsSettingsRegionPreset;

class OpsSettingsPage extends Page
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Platform Settings';

    protected static ?int $navigationSort = 50;

    protected static ?string $title = 'Platform Settings';

    protected static ?string $slug = 'ops-settings';

    public ?array $importPayload = [];

    public static function canAccess(): bool
    {
        return Gate::check('ops.settings.view');
    }

    public static function getNavigationBadge(): ?string
    {
        return sprintf('%d%%', app(OpsSettingsManager::class)->completionPercent());
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return app(OpsSettingsManager::class)->isComplete() ? 'success' : 'warning';
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
        $manager = $this->manager();

        return $schema->components([
            Section::make('Platform Settings')
                ->description('Manage operator-facing defaults, communication channels, legal content, and reusable website fallbacks from one tabbed workspace. Each group keeps its own save boundary so audit and change tracking stay explicit.')
                ->icon('heroicon-o-adjustments-horizontal')
                ->schema([
                    Section::make('Readiness Snapshot')
                        ->description('Use these helpers to see whether the platform defaults are complete enough for downstream reuse.')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Section::make('Current completion state')
                                        ->description($manager->isComplete()
                                            ? 'All required baseline fields are currently filled.'
                                            : 'Some required baseline fields are still missing and should be completed before wider package reuse.')
                                        ->schema([
                                            Text::make(sprintf('%d%% workspace completion', $manager->completionPercent()))
                                                ->badge()
                                                ->color($manager->isComplete() ? 'success' : 'warning'),
                                        ]),
                                    Section::make('Missing required fields')
                                        ->description($this->missingRequiredFieldsDescription())
                                        ->schema([
                                            Text::make($this->readinessSummary())
                                                ->badge()
                                                ->color($manager->isComplete() ? 'success' : 'warning'),
                                        ]),
                                    Section::make('Current history window')
                                        ->description('Recent changes are shown below and per tab using the package-owned audit history reader.')
                                        ->schema([
                                            Text::make(sprintf(
                                                'Showing up to %d recent changes',
                                                (int) config('ops-settings.workspace.history_limit', 20),
                                            ))
                                                ->badge()
                                                ->color('gray'),
                                        ]),
                                ]),
                        ]),
                    Section::make('Workspace Overview')
                        ->description('Each settings group shows its own completion state, latest change, and save boundary so operators can work through the platform settings methodically.')
                        ->schema([
                            Grid::make(3)
                                ->schema($this->statusCards()),
                        ]),
                    Tabs::make('Settings Tabs')
                        ->tabs($this->settingsTabs())
                        ->vertical()
                        ->persistTabInQueryString('tab')
                        ->columnSpanFull(),
                    Section::make('Recent Changes')
                        ->description('Latest package-owned audit entries for settings updates in a compact table-style overview for operators.')
                        ->schema($this->recentHistoryTable()),
                ]),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('applyPreset')
                ->label('Apply Region Preset')
                ->icon('heroicon-o-map')
                ->schema([
                    Select::make('preset')
                        ->label('Preset')
                        ->options($this->presetOptions())
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $preset = Arr::get($data, 'preset');

                    if (! is_string($preset) || $preset === '') {
                        return;
                    }

                    $this->applyPreset($preset);
                }),
            Action::make('exportSnapshot')
                ->label('Export Snapshot')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => response()->streamDownload(function (): void {
                    echo json_encode($this->manager()->exportSnapshot(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                }, 'ops-settings-snapshot.json', [
                    'Content-Type' => 'application/json',
                ])),
            Action::make('importSnapshot')
                ->label('Import Snapshot')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Import Settings Snapshot')
                ->modalDescription('Paste a previously exported JSON snapshot. The imported values are applied to the current workspace state first, so you can review each tab before saving it.')
                ->schema([
                    Textarea::make('snapshot')
                        ->label('Snapshot JSON')
                        ->rows(16)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $snapshot = Arr::get($data, 'snapshot');

                    if (! is_string($snapshot) || trim($snapshot) === '') {
                        return;
                    }

                    $this->importSnapshot($snapshot);
                }),
        ];
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
            $status = $this->manager()->groupStatuses()[$group->value] ?? null;

            $tabs[$group->value] = Tab::make($group->label())
                ->icon($group->icon())
                ->badge($status['completion_percent'] ?? null)
                ->badgeColor($this->badgeColorForStatus((string) ($status['status'] ?? 'empty')))
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
                                    Section::make('Status')
                                        ->description('This group keeps its own completion state and latest audit metadata.')
                                        ->schema($this->groupStatusDetails($group)),
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

        $this->refreshWorkspaceState();
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
        $missing = $this->manager()->missingRequiredFields();

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

    private function applyPreset(string $preset): void
    {
        $values = $this->manager()->presetValues($preset);

        if ($values === []) {
            Notification::make()
                ->title('Preset could not be applied.')
                ->danger()
                ->send();

            return;
        }

        foreach ($values as $group => $attributes) {
            if (! isset($this->data[$group])) {
                continue;
            }

            $this->data[$group] = array_replace($this->data[$group], $attributes);
        }

        Notification::make()
            ->title('Preset applied to the workspace.')
            ->body('Review the updated fields and save each affected tab when you are ready.')
            ->success()
            ->send();
    }

    private function importSnapshot(string $snapshot): void
    {
        try {
            $decoded = json_decode($snapshot, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            Notification::make()
                ->title('Snapshot JSON is invalid.')
                ->danger()
                ->send();

            return;
        }

        $groups = Arr::get($decoded, 'groups', Arr::get($decoded, 'snapshot', $decoded));

        if (! is_array($groups)) {
            Notification::make()
                ->title('Snapshot payload is missing grouped settings data.')
                ->danger()
                ->send();

            return;
        }

        foreach (OpsSettingsGroup::cases() as $group) {
            $payload = Arr::get($groups, $group->value);

            if (! is_array($payload)) {
                continue;
            }

            $this->data[$group->value] = array_replace(
                $this->data[$group->value] ?? [],
                Arr::only($payload, $group->approvedProperties()),
            );
        }

        Notification::make()
            ->title('Snapshot imported into the workspace.')
            ->body('Imported values are now loaded into the current form state. Save each tab to persist the reviewed changes.')
            ->success()
            ->send();
    }

    /**
     * @return array<int, Section>
     */
    private function statusCards(): array
    {
        return collect($this->manager()->groupStatuses())
            ->map(function (array $status): Section {
                $latestChange = $status['latest_change'];

                return Section::make((string) $status['label'])
                    ->description((string) $status['description'])
                    ->schema([
                        Text::make(sprintf('%d%% complete', (int) $status['completion_percent']))
                            ->badge()
                            ->color($this->badgeColorForStatus((string) $status['status'])),
                        Text::make($this->missingFieldsLine($status))
                            ->badge()
                            ->color(($status['missing_required'] ?? []) === [] ? 'success' : 'warning'),
                        Text::make($this->latestChangeLine($latestChange))
                            ->color('gray'),
                    ]);
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, Grid|Section>
     */
    private function recentHistoryTable(): array
    {
        $history = $this->manager()->recentHistory(limit: (int) config('ops-settings.workspace.history_limit', 20));

        if ($history->isEmpty()) {
            return [
                Section::make('No recent changes')
                    ->description('No package-owned audit entries are currently available for the ops settings workspace.')
                    ->schema([]),
            ];
        }

        return array_merge([
            Grid::make(12)
                ->schema([
                    Text::make('Group')
                        ->badge()
                        ->color('gray')
                        ->columnSpan(2),
                    Text::make('Changed Keys')
                        ->badge()
                        ->color('gray')
                        ->columnSpan(4),
                    Text::make('Actor / Source')
                        ->badge()
                        ->color('gray')
                        ->columnSpan(3),
                    Text::make('Updated At')
                        ->badge()
                        ->color('gray')
                        ->columnSpan(3),
                ]),
        ], $history
            ->take(6)
            ->map(function (array $entry): Grid {
                $group = is_string($entry['group'] ?? null)
                    ? OpsSettingsGroup::fromValue((string) $entry['group'])
                    : null;

                return Grid::make(12)
                    ->schema([
                        Text::make($group?->label() ?? 'Unknown group')
                            ->badge()
                            ->color($group === null ? 'gray' : 'info')
                            ->columnSpan(2),
                        Text::make($this->historyChangedKeysValue($entry))
                            ->color('gray')
                            ->columnSpan(4),
                        Text::make($this->historyActorSourceLine($entry))
                            ->color('gray')
                            ->columnSpan(3),
                        Text::make($this->historyTimestampLine($entry))
                            ->color('gray')
                            ->columnSpan(3),
                    ]);
            })
            ->values()
            ->all());
    }

    /**
     * @return array<int, Text>
     */
    private function groupStatusDetails(OpsSettingsGroup $group): array
    {
        $status = $this->manager()->groupStatuses()[$group->value] ?? [];

        return [
            Text::make(sprintf('%d%% complete', (int) ($status['completion_percent'] ?? 0)))
                ->badge()
                ->color($this->badgeColorForStatus((string) ($status['status'] ?? 'empty'))),
            Text::make($this->missingFieldsLine($status))
                ->badge()
                ->color(($status['missing_required'] ?? []) === [] ? 'success' : 'warning'),
            Text::make($this->latestChangeLine($status['latest_change'] ?? null))
                ->color('gray'),
        ];
    }

    private function readinessSummary(): string
    {
        return $this->manager()->isComplete()
            ? 'Required baseline complete'
            : 'Required baseline still incomplete';
    }

    /**
     * @param  array<string, mixed>  $status
     */
    private function missingFieldsLine(array $status): string
    {
        $missing = $status['missing_required'] ?? [];

        if (! is_array($missing) || $missing === []) {
            return 'No required fields missing';
        }

        return 'Missing: '.implode(', ', $missing);
    }

    /**
     * @param  array<string, mixed>|null  $latestChange
     */
    private function latestChangeLine(?array $latestChange): string
    {
        if ($latestChange === null) {
            return 'No recorded changes yet';
        }

        $createdAt = $latestChange['created_at'] ?? null;
        $source = $latestChange['source'] ?? 'unknown source';

        if ($createdAt instanceof \DateTimeInterface) {
            return sprintf('Last change via %s at %s', $source, $createdAt->format('Y-m-d H:i'));
        }

        return sprintf('Last change via %s', $source);
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function historyChangedKeysValue(array $entry): string
    {
        $keys = $entry['changed_keys'] ?? [];

        if (! is_array($keys) || $keys === []) {
            return 'No changed keys recorded';
        }

        return implode(', ', $keys);
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function historyActorSourceLine(array $entry): string
    {
        $source = $entry['source'] ?? 'unknown source';
        $actorId = $entry['actor_id'] ?? 'system';

        return sprintf('%s via %s', (string) $actorId, (string) $source);
    }

    /**
     * @param  array<string, mixed>  $entry
     */
    private function historyTimestampLine(array $entry): string
    {
        $createdAt = $entry['created_at'] ?? null;

        if ($createdAt instanceof \DateTimeInterface) {
            return $createdAt->format('Y-m-d H:i');
        }

        return 'Unknown time';
    }

    private function badgeColorForStatus(string $status): string
    {
        return match ($status) {
            'ready' => 'success',
            'incomplete' => 'warning',
            default => 'gray',
        };
    }

    /**
     * @return array<string, string>
     */
    private function presetOptions(): array
    {
        return collect(config('ops-settings.workspace.presets', []))
            ->filter(fn (mixed $preset): bool => is_string($preset) && $preset !== '')
            ->mapWithKeys(fn (string $preset): array => [$preset => OpsSettingsRegionPreset::options()[$preset] ?? Str::upper($preset)])
            ->all();
    }

    private function refreshWorkspaceState(): void
    {
        $this->manager()->invalidateAll();

        foreach (OpsSettingsGroup::cases() as $group) {
            $this->data[$group->value] = OpsSettingsPageSchema::currentData($this->manager(), $group);
        }
    }

    private function manager(): OpsSettingsManager
    {
        return app(OpsSettingsManager::class);
    }
}
