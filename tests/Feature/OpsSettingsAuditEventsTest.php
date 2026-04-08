<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use YezzMedia\OpsSettings\Events\OpsSettingsSnapshotExported;
use YezzMedia\OpsSettings\Events\OpsSettingsSnapshotImported;
use YezzMedia\OpsSettings\Filament\Pages\OpsSettingsPage;

it('dispatches a package-owned audit event when exporting a snapshot', function (): void {
    Event::fake([OpsSettingsSnapshotExported::class]);

    Gate::define('ops.settings.view', fn ($user = null) => true);

    $page = app(OpsSettingsPage::class);
    $page->mount();

    $response = $page->exportSnapshot();

    expect($response)->toBeInstanceOf(StreamedResponse::class);

    Event::assertDispatched(OpsSettingsSnapshotExported::class, function (OpsSettingsSnapshotExported $event): bool {
        return $event->source === 'ops_panel'
            && $event->groupCount === 6
            && $event->completionPercent >= 0;
    });
});

it('dispatches a package-owned audit event when importing a snapshot', function (): void {
    Event::fake([OpsSettingsSnapshotImported::class]);

    Gate::define('ops.settings.view', fn ($user = null) => true);

    $page = app(OpsSettingsPage::class);
    $page->mount();

    $page->importSnapshot(json_encode([
        'groups' => [
            'identity' => [
                'name' => 'Imported Name',
            ],
            'contact' => [
                'support_email' => 'support@example.com',
            ],
        ],
    ], JSON_THROW_ON_ERROR));

    Event::assertDispatched(OpsSettingsSnapshotImported::class, function (OpsSettingsSnapshotImported $event): bool {
        return $event->source === 'ops_panel'
            && $event->importedGroups === ['identity', 'contact'];
    });

    expect($page->data['identity']['name'] ?? null)->toBe('Imported Name')
        ->and($page->data['contact']['support_email'] ?? null)->toBe('support@example.com');
});
