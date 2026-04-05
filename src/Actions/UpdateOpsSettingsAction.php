<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Actions;

use InvalidArgumentException;
use YezzMedia\OpsSettings\Events\OpsSettingsUpdated;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

/**
 * Package-owned mutation boundary for settings updates.
 *
 * One call targets exactly one approved settings group.
 * Unsupported groups and unsupported attributes fail fast with InvalidArgumentException.
 * Authorization enforcement is the caller's responsibility.
 */
final class UpdateOpsSettingsAction
{
    public function __construct(private readonly OpsSettingsManager $manager) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function execute(
        OpsSettingsGroup $group,
        array $attributes,
        int|string|null $actorId = null,
        array $context = [],
        ?string $source = null,
    ): void {
        $approvedProperties = $group->approvedProperties();

        foreach (array_keys($attributes) as $key) {
            if (! in_array($key, $approvedProperties, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Attribute [%s] is not an approved property of settings group [%s]. Approved properties: %s.',
                    $key,
                    $group->value,
                    implode(', ', $approvedProperties),
                ));
            }
        }

        $settings = app($group->settingsClass());

        foreach ($attributes as $key => $value) {
            $settings->{$key} = $value;
        }

        $settings->save();

        $this->manager->invalidate($group);

        event(new OpsSettingsUpdated(
            group: $group,
            changedKeys: array_keys($attributes),
            actorId: $actorId,
            context: $context,
            source: $source,
        ));
    }
}
