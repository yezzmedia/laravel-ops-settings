<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Events;

use YezzMedia\OpsSettings\Support\OpsSettingsGroup;

/**
 * Runtime event emitted after a successful ops settings mutation.
 *
 * Emitted only after the mutation has been persisted and the cache invalidated.
 * Use as the package-owned signal for audit bridging and downstream cache invalidation.
 * changedKeys contains the attribute keys passed in the mutation request.
 */
final class OpsSettingsUpdated
{
    /**
     * @param  array<int, string>  $changedKeys
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     * @param  array<string, mixed>  $context
     */
    public function __construct(
        public readonly OpsSettingsGroup $group,
        public readonly array $changedKeys,
        public readonly int|string|null $actorId,
        public readonly array $oldValues,
        public readonly array $newValues,
        public readonly array $context,
        public readonly ?string $source,
    ) {}
}
