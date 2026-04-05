<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
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
        /** @var OperatorIdentitySettings */
        return $this->resolve(OpsSettingsGroup::Identity);
    }

    public function contact(): PlatformContactSettings
    {
        /** @var PlatformContactSettings */
        return $this->resolve(OpsSettingsGroup::Contact);
    }

    public function brand(): PlatformBrandSettings
    {
        /** @var PlatformBrandSettings */
        return $this->resolve(OpsSettingsGroup::Brand);
    }

    public function social(): PlatformSocialSettings
    {
        /** @var PlatformSocialSettings */
        return $this->resolve(OpsSettingsGroup::Social);
    }

    public function legal(): PlatformLegalSettings
    {
        /** @var PlatformLegalSettings */
        return $this->resolve(OpsSettingsGroup::Legal);
    }

    public function websiteDefaults(): PlatformWebsiteDefaultsSettings
    {
        /** @var PlatformWebsiteDefaultsSettings */
        return $this->resolve(OpsSettingsGroup::WebsiteDefaults);
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
     * Resolves a settings instance for the given group with cache-forever strategy.
     */
    private function resolve(OpsSettingsGroup $group): mixed
    {
        if (isset($this->memo[$group->value])) {
            return $this->memo[$group->value];
        }

        if (! $this->cacheEnabled) {
            $settings = app($group->settingsClass());
            $this->memo[$group->value] = $settings;

            return $settings;
        }

        $settings = $this->cacheRepository->rememberForever(
            $group->cacheKey(),
            fn () => app($group->settingsClass()),
        );

        $this->memo[$group->value] = $settings;

        return $settings;
    }
}
