<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Events;

final class OpsSettingsSnapshotExported
{
    public function __construct(
        public readonly int $completionPercent,
        public readonly int $groupCount,
        public readonly int|string|null $actorId,
        public readonly string $exportedAt,
        public readonly ?string $source,
    ) {}
}
