<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class OpsSettingsHistoryReader
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recent(?OpsSettingsGroup $group = null, int $limit = 20): Collection
    {
        if (! class_exists('Spatie\\Activitylog\\Models\\Activity')) {
            return collect();
        }

        $activityClass = 'Spatie\\Activitylog\\Models\\Activity';

        if (! Schema::hasTable((new $activityClass)->getTable())) {
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

        return $query->get()->map(function (object $activity): array {
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
        })->values();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function latestForGroup(OpsSettingsGroup $group): ?array
    {
        return $this->recent($group, 1)->first();
    }
}
