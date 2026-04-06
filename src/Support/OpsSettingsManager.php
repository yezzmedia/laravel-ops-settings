<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use InvalidArgumentException;
use Spatie\LaravelSettings\Settings;
use YezzMedia\OpsSettings\Settings\OperatorIdentitySettings;
use YezzMedia\OpsSettings\Settings\PlatformBrandSettings;
use YezzMedia\OpsSettings\Settings\PlatformContactSettings;
use YezzMedia\OpsSettings\Settings\PlatformLegalSettings;
use YezzMedia\OpsSettings\Settings\PlatformSocialSettings;
use YezzMedia\OpsSettings\Settings\PlatformWebsiteDefaultsSettings;

/**
 * Preferred stable cross-package read API for ops settings.
 *
 * Provides cache-aware grouped access to operator-managed global defaults.
 * Cache keys follow the pattern: ops_settings.{group_value}.
 * Downstream packages should prefer this manager over direct storage access.
 */
class OpsSettingsManager
{
    private CacheRepository $cacheRepository;

    /** @var array<string, mixed> Per-request memoization to prevent redundant cache/DB hits. */
    private array $memo = [];

    public function __construct(
        private readonly CacheFactory $cacheFactory,
        private readonly bool $cacheEnabled,
        private readonly ?string $cacheStore,
    ) {
        $this->cacheRepository = $this->cacheFactory->store($this->cacheStore);
    }

    public function identity(): OperatorIdentitySettings
    {
        return $this->resolve(OpsSettingsGroup::Identity, OperatorIdentitySettings::class);
    }

    public function contact(): PlatformContactSettings
    {
        return $this->resolve(OpsSettingsGroup::Contact, PlatformContactSettings::class);
    }

    public function brand(): PlatformBrandSettings
    {
        return $this->resolve(OpsSettingsGroup::Brand, PlatformBrandSettings::class);
    }

    public function social(): PlatformSocialSettings
    {
        return $this->resolve(OpsSettingsGroup::Social, PlatformSocialSettings::class);
    }

    public function legal(): PlatformLegalSettings
    {
        return $this->resolve(OpsSettingsGroup::Legal, PlatformLegalSettings::class);
    }

    public function websiteDefaults(): PlatformWebsiteDefaultsSettings
    {
        return $this->resolve(OpsSettingsGroup::WebsiteDefaults, PlatformWebsiteDefaultsSettings::class);
    }

    /**
     * Stable alias for obtaining all grouped settings at once.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->snapshot();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function snapshot(): array
    {
        $snapshot = [];

        foreach (OpsSettingsGroup::cases() as $group) {
            $snapshot[$group->value] = $this->settingsForGroup($group)->toArray();
        }

        return $snapshot;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function publicPayload(): array
    {
        $payload = [];

        foreach (OpsSettingsGroup::cases() as $group) {
            $settings = $this->settingsForGroup($group)->toArray();
            $payload[$group->value] = array_intersect_key(
                $settings,
                array_flip($group->publicProperties()),
            );
        }

        return $payload;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function missingRequiredFields(): array
    {
        $missing = [];

        foreach (OpsSettingsGroup::cases() as $group) {
            $settings = $this->settingsForGroup($group)->toArray();
            $missingFields = array_values(array_filter(
                $group->requiredProperties(),
                static fn (string $property): bool => blank($settings[$property] ?? null),
            ));

            if ($missingFields !== []) {
                $missing[$group->value] = $missingFields;
            }
        }

        return $missing;
    }

    public function isComplete(): bool
    {
        return $this->missingRequiredFields() === [];
    }

    /**
     * Invalidates the package-owned cache for a specific settings group.
     * Called by UpdateOpsSettingsAction after a successful mutation.
     */
    public function invalidate(OpsSettingsGroup $group): void
    {
        unset($this->memo[$group->value]);
        $this->cacheRepository->forget($group->cacheKey());
    }

    /**
     * Invalidates all package-owned settings caches.
     */
    public function invalidateAll(): void
    {
        foreach (OpsSettingsGroup::cases() as $group) {
            $this->invalidate($group);
        }
    }

    /**
     * @template TSettings of Settings
     *
     * @param  class-string<TSettings>  $settingsClass
     * @return TSettings
     */
    private function resolve(OpsSettingsGroup $group, string $settingsClass): Settings
    {
        if (isset($this->memo[$group->value])) {
            return $this->ensureSettingsType($this->memo[$group->value], $settingsClass);
        }

        $settings = app($settingsClass);

        if (! $this->cacheEnabled) {
            $this->memo[$group->value] = $settings;

            return $settings;
        }

        $cachedPayload = $this->cacheRepository->get($group->cacheKey());

        if (is_array($cachedPayload)) {
            $settings->fill($cachedPayload);
            $settings = $this->ensureSettingsType($settings, $settingsClass);
        } else {
            $this->cacheRepository->forever($group->cacheKey(), $settings->toArray());
        }

        $this->memo[$group->value] = $settings;

        return $settings;
    }

    private function settingsForGroup(OpsSettingsGroup $group): Settings
    {
        return match ($group) {
            OpsSettingsGroup::Identity => $this->identity(),
            OpsSettingsGroup::Contact => $this->contact(),
            OpsSettingsGroup::Brand => $this->brand(),
            OpsSettingsGroup::Social => $this->social(),
            OpsSettingsGroup::Legal => $this->legal(),
            OpsSettingsGroup::WebsiteDefaults => $this->websiteDefaults(),
        };
    }

    /**
     * @template TSettings of Settings
     *
     * @param  class-string<TSettings>  $settingsClass
     * @return TSettings
     */
    private function ensureSettingsType(mixed $settings, string $settingsClass): Settings
    {
        if ($settings instanceof $settingsClass) {
            return $settings;
        }

        throw new InvalidArgumentException("Resolved settings must be an instance of [{$settingsClass}].");
    }
}
