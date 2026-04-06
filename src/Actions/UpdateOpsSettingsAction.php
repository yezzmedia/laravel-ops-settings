<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Actions;

use InvalidArgumentException;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;
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
    public function __construct(
        private readonly OpsSettingsManager $manager,
        private readonly SettingsRepository $repository,
    ) {}

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

        $this->seedMissingApprovedProperties($group);

        /** @var Settings $settings */
        $settings = app($group->settingsClass());
        $oldValues = [];

        foreach ($attributes as $key => $value) {
            $oldValues[$key] = $settings->{$key};
            $settings->{$key} = $value;
        }

        $settings->save();

        $newValues = [];

        foreach (array_keys($attributes) as $key) {
            $newValues[$key] = $settings->{$key};
        }

        $this->manager->invalidate($group);

        event(new OpsSettingsUpdated(
            group: $group,
            changedKeys: array_keys($attributes),
            actorId: $actorId,
            oldValues: $oldValues,
            newValues: $newValues,
            context: $context,
            source: $source,
        ));
    }

    private function seedMissingApprovedProperties(OpsSettingsGroup $group): void
    {
        foreach ($group->approvedProperties() as $property) {
            if ($this->repository->checkIfPropertyExists($group->value, $property)) {
                continue;
            }

            $this->repository->createProperty($group->value, $property, null);
        }
    }
}
