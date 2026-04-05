<?php

declare(strict_types=1);

use YezzMedia\OpsSettings\Events\OpsSettingsUpdated;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;

it('holds the correct payload after construction', function (): void {
    $event = new OpsSettingsUpdated(
        group: OpsSettingsGroup::Brand,
        changedKeys: ['brand_name', 'primary_color'],
        actorId: 7,
        context: ['request_id' => 'abc'],
        source: 'panel',
    );

    expect($event->group)->toBe(OpsSettingsGroup::Brand)
        ->and($event->changedKeys)->toBe(['brand_name', 'primary_color'])
        ->and($event->actorId)->toBe(7)
        ->and($event->context)->toBe(['request_id' => 'abc'])
        ->and($event->source)->toBe('panel');
});

it('allows null actorId and source', function (): void {
    $event = new OpsSettingsUpdated(
        group: OpsSettingsGroup::Legal,
        changedKeys: ['vat_id'],
        actorId: null,
        context: [],
        source: null,
    );

    expect($event->actorId)->toBeNull()
        ->and($event->source)->toBeNull()
        ->and($event->context)->toBe([]);
});
