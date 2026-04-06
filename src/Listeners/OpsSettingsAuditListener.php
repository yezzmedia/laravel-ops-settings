<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Listeners;

use YezzMedia\OpsSettings\Contracts\OpsSettingsAuditWriter;
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
}
