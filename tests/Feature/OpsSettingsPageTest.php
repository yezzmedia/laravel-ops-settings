<?php

declare(strict_types=1);

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Spatie\Activitylog\Models\Activity;
use YezzMedia\OpsSettings\Filament\Pages\OpsSettingsPage;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

function testOpsSettingsUser(string $password = 'secret'): Authenticatable
{
    $user = new class extends Authenticatable {};

    $user->forceFill([
        'id' => 7,
        'name' => 'Ops Settings Tester',
        'email' => 'settings@example.com',
        'password' => Hash::make($password),
    ]);

    return $user;
}

it('renders recent changes as the last section with a table-style grid layout', function (): void {
    if (! class_exists(Activity::class)) {
        $this->markTestSkipped('spatie/laravel-activitylog is not installed in the package environment.');
    }

    if (! SchemaFacade::hasTable('activity_log')) {
        SchemaFacade::create('activity_log', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->string('event')->nullable();
            $table->json('attribute_changes')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    Activity::query()->create([
        'log_name' => 'ops_settings',
        'description' => 'ops.settings.updated',
        'event' => 'ops.settings.updated',
        'properties' => [
            'group' => 'identity',
            'changed_keys' => ['name', 'platform_label'],
            'actor_id' => 7,
            'source' => 'ops_panel',
        ],
    ]);

    Gate::define('ops.settings.view', fn ($user = null) => true);

    $page = app(OpsSettingsPage::class);
    $page->mount();

    $schema = $page->content(Schema::make($page));
    $components = $schema->getComponents(withActions: false, withHidden: true);

    expect($components)->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(Section::class);

    /** @var Section $workspace */
    $workspace = $components[0];
    $workspaceComponents = $workspace->getDefaultChildComponents();

    expect($workspaceComponents[3])->toBeInstanceOf(Section::class)
        ->and($workspaceComponents[3]->getHeading())->toBe('Recent Changes');

    /** @var Section $recentChanges */
    $recentChanges = $workspaceComponents[3];
    $recentChangeComponents = $recentChanges->getDefaultChildComponents();

    expect($recentChangeComponents[0])->toBeInstanceOf(Grid::class);

    /** @var Grid $headerGrid */
    $headerGrid = $recentChangeComponents[0];
    $headerColumns = $headerGrid->getDefaultChildComponents();

    expect($headerColumns)->toHaveCount(4)
        ->and($headerColumns[0])->toBeInstanceOf(Text::class)
        ->and($headerColumns[1])->toBeInstanceOf(Text::class)
        ->and($headerColumns[2])->toBeInstanceOf(Text::class)
        ->and($headerColumns[3])->toBeInstanceOf(Text::class)
        ->and($headerColumns[0]->getContent())->toBe('Group')
        ->and($headerColumns[1]->getContent())->toBe('Changed Keys')
        ->and($headerColumns[2]->getContent())->toBe('Actor / Source')
        ->and($headerColumns[3]->getContent())->toBe('Updated At');
});

it('requires password confirmation before saving settings changes', function (): void {
    Gate::define('ops.settings.view', fn ($user = null) => true);
    Gate::define('ops.settings.manage', fn ($user = null) => true);

    Auth::guard('web')->setUser(testOpsSettingsUser());

    $page = app(OpsSettingsPage::class);
    $page->mount();
    $page->data['identity']['name'] = 'Locked Name';

    $page->saveIdentity();

    expect(app(OpsSettingsManager::class)->identity()->name)->not->toBe('Locked Name');

    expect($page->confirmPassword('secret'))->toBeTrue();

    $page->saveIdentity();

    expect(app(OpsSettingsManager::class)->identity()->name)->toBe('Locked Name');
});

it('rejects an invalid current password confirmation', function (): void {
    Gate::define('ops.settings.view', fn ($user = null) => true);
    Gate::define('ops.settings.manage', fn ($user = null) => true);

    Auth::guard('web')->setUser(testOpsSettingsUser());

    $page = app(OpsSettingsPage::class);
    $page->mount();

    expect($page->confirmPassword('wrong-secret'))->toBeFalse()
        ->and(session()->has('ops-settings.password_confirmation.confirmed_at'))->toBeFalse();
});

it('requires password confirmation before applying presets and importing snapshots', function (): void {
    Gate::define('ops.settings.view', fn ($user = null) => true);
    Gate::define('ops.settings.manage', fn ($user = null) => true);

    Auth::guard('web')->setUser(testOpsSettingsUser());

    $page = app(OpsSettingsPage::class);
    $page->mount();

    expect($page->data['contact']['country_code'] ?? null)->toBeNull();

    $page->applyPreset('de');

    expect($page->data['contact']['country_code'] ?? null)->toBeNull();

    expect($page->confirmPassword('secret'))->toBeTrue();

    $page->applyPreset('de');

    expect($page->data['contact']['country_code'] ?? null)->toBe('DE');

    session()->forget('ops-settings.password_confirmation.confirmed_at');

    $page->importSnapshot(json_encode([
        'groups' => [
            'identity' => [
                'name' => 'Imported Without Confirmation',
            ],
        ],
    ], JSON_THROW_ON_ERROR));

    expect($page->data['identity']['name'] ?? null)->not->toBe('Imported Without Confirmation');

    expect($page->confirmPassword('secret'))->toBeTrue();

    $page->importSnapshot(json_encode([
        'groups' => [
            'identity' => [
                'name' => 'Imported With Confirmation',
            ],
        ],
    ], JSON_THROW_ON_ERROR));

    expect($page->data['identity']['name'] ?? null)->toBe('Imported With Confirmation');
});
