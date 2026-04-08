<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class OpsSettingsHistoryReader
{
    private ?bool $activityLogTableAvailable = null;

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recent(?OpsSettingsGroup $group = null, int $limit = 20): Collection
    {
        $activityClass = $this->activityClass();

        if ($activityClass === null || ! $this->activityLogTableAvailable($activityClass)) {
            return collect();
        }

        $query = $activityClass::query()
            ->where('log_name', 'ops_settings')
            ->where('event', 'ops.settings.updated')
            ->latest('id')
            ->limit($limit);

        if ($group !== null) {
            $query->where('properties->group', $group->value);
        }

        return $query->get()->map(fn (object $activity): array => $this->mapActivity($activity))->values();
    }

    /**
     * @param  array<int, OpsSettingsGroup>  $groups
     * @return array<string, array<string, mixed>|null>
     */
    public function latestForGroups(array $groups): array
    {
        $groupValues = array_values(array_unique(array_map(
            static fn (OpsSettingsGroup $group): string => $group->value,
            $groups,
        )));

        if ($groupValues === []) {
            return [];
        }

        $activityClass = $this->activityClass();

        if ($activityClass === null || ! $this->activityLogTableAvailable($activityClass)) {
            return collect($groupValues)
                ->mapWithKeys(static fn (string $group): array => [$group => null])
                ->all();
        }

        $latestEntries = $activityClass::query()
            ->where('log_name', 'ops_settings')
            ->where('event', 'ops.settings.updated')
            ->whereIn('properties->group', $groupValues)
            ->latest('id')
            ->get()
            ->map(fn (object $activity): array => $this->mapActivity($activity))
            ->filter(static fn (array $entry): bool => is_string($entry['group'] ?? null))
            ->groupBy('group')
            ->map(static fn (Collection $entries): ?array => $entries->first())
            ->all();

        return collect($groupValues)
            ->mapWithKeys(static fn (string $group): array => [$group => $latestEntries[$group] ?? null])
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function latestForGroup(OpsSettingsGroup $group): ?array
    {
        return $this->latestForGroups([$group])[$group->value] ?? null;
    }

    private function activityClass(): ?string
    {
        $activityClass = 'Spatie\\Activitylog\\Models\\Activity';

        return class_exists($activityClass) ? $activityClass : null;
    }

    private function activityLogTableAvailable(string $activityClass): bool
    {
        if ($this->activityLogTableAvailable !== null) {
            return $this->activityLogTableAvailable;
        }

        return $this->activityLogTableAvailable = Schema::hasTable((new $activityClass)->getTable());
    }

    /**
     * @return array<string, mixed>
     */
    private function mapActivity(object $activity): array
    {
        $properties = method_exists($activity, 'getProperties')
            ? $activity->getProperties()
            : $activity->properties;

        $group = data_get($properties, 'group');

        return [
            'group' => is_string($group) ? $group : null,
            'changed_keys' => array_values((array) data_get($properties, 'changed_keys', [])),
            'old_values' => (array) data_get($properties, 'old_values', []),
            'new_values' => (array) data_get($properties, 'new_values', []),
            'actor_id' => data_get($properties, 'actor_id'),
            'source' => data_get($properties, 'source'),
            'created_at' => $activity->created_at,
        ];
    }
}
