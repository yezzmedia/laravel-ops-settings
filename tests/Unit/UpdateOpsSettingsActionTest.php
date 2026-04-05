<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use YezzMedia\OpsSettings\Actions\UpdateOpsSettingsAction;
use YezzMedia\OpsSettings\Events\OpsSettingsUpdated;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

beforeEach(function (): void {
    // Seed the settings store with the base values the action needs.
    DB::table('settings')->insert([
        ['group' => 'identity', 'name' => 'name', 'locked' => false, 'payload' => json_encode('')],
        ['group' => 'identity', 'name' => 'platform_label', 'locked' => false, 'payload' => json_encode(null)],
    ]);
});

it('persists the updated attribute via the settings class', function (): void {
    $action = app(UpdateOpsSettingsAction::class);

    $action->execute(OpsSettingsGroup::Identity, ['name' => 'Acme Corp']);

    expect(DB::table('settings')->where('group', 'identity')->where('name', 'name')->value('payload'))
        ->toBe(json_encode('Acme Corp'));
});

it('invalidates the manager cache after a successful mutation', function (): void {
    config()->set('ops-settings.cache.enabled', true);
    app()->forgetInstance(OpsSettingsManager::class);

    $manager = app(OpsSettingsManager::class);
    Cache::put(OpsSettingsGroup::Identity->cacheKey(), 'stale');

    app(UpdateOpsSettingsAction::class)->execute(OpsSettingsGroup::Identity, ['name' => 'New Name']);

    expect(Cache::has(OpsSettingsGroup::Identity->cacheKey()))->toBeFalse();
});

it('dispatches OpsSettingsUpdated after a successful mutation', function (): void {
    Event::fake([OpsSettingsUpdated::class]);

    app(UpdateOpsSettingsAction::class)->execute(
        group: OpsSettingsGroup::Identity,
        attributes: ['name' => 'Dispatched'],
        actorId: 42,
        context: ['source_ip' => '127.0.0.1'],
        source: 'test',
    );

    Event::assertDispatched(OpsSettingsUpdated::class, function (OpsSettingsUpdated $event): bool {
        return $event->group === OpsSettingsGroup::Identity
            && $event->changedKeys === ['name']
            && $event->actorId === 42
            && $event->context === ['source_ip' => '127.0.0.1']
            && $event->source === 'test';
    });
});

it('fails fast when an unknown attribute is passed', function (): void {
    expect(fn () => app(UpdateOpsSettingsAction::class)->execute(
        OpsSettingsGroup::Identity,
        ['unknown_field' => 'value'],
    ))->toThrow(InvalidArgumentException::class, 'Attribute [unknown_field] is not an approved property of settings group [identity].');
});
