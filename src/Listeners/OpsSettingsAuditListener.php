<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Listeners;

use YezzMedia\OpsSettings\Contracts\OpsSettingsAuditWriter;
use YezzMedia\OpsSettings\Events\OpsSettingsSnapshotExported;
use YezzMedia\OpsSettings\Events\OpsSettingsSnapshotImported;
use YezzMedia\OpsSettings\Events\OpsSettingsUpdated;

final readonly class OpsSettingsAuditListener
{
    public function __construct(
        private OpsSettingsAuditWriter $writer,
    ) {}

    public function handleSettingsUpdated(OpsSettingsUpdated $event): void
    {
        $this->writer->write('ops.settings.updated', [
            'group' => $event->group->value,
            'changed_keys' => $event->changedKeys,
            'actor_id' => $event->actorId,
            'old_values' => $event->oldValues,
            'new_values' => $event->newValues,
            'context' => $event->context,
            'source' => $event->source,
        ]);
    }

    public function handleSnapshotExported(OpsSettingsSnapshotExported $event): void
    {
        $this->writer->write('ops.settings.snapshot_exported', [
            'completion_percent' => $event->completionPercent,
            'group_count' => $event->groupCount,
            'actor_id' => $event->actorId,
            'exported_at' => $event->exportedAt,
            'source' => $event->source,
        ]);
    }

    public function handleSnapshotImported(OpsSettingsSnapshotImported $event): void
    {
        $this->writer->write('ops.settings.snapshot_imported', [
            'imported_groups' => $event->importedGroups,
            'imported_group_count' => count($event->importedGroups),
            'actor_id' => $event->actorId,
            'source' => $event->source,
        ]);
    }
}
