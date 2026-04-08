<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Events;

final class OpsSettingsSnapshotImported
{
    /**
     * @param  array<int, string>  $importedGroups
     */
    public function __construct(
        public readonly array $importedGroups,
        public readonly int|string|null $actorId,
        public readonly ?string $source,
    ) {}
}
