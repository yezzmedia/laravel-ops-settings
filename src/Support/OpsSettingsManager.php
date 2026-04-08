<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
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

    /** @var array<string, array<int, string>>|null */
    private ?array $missingRequiredFieldsMemo = null;

    /** @var array<string, array<string, mixed>>|null */
    private ?array $groupStatusesMemo = null;

    private ?int $completionPercentMemo = null;

    /** @var array<string, Collection<int, array<string, mixed>>> */
    private array $recentHistoryMemo = [];

    public function __construct(
        private readonly CacheFactory $cacheFactory,
        private readonly OpsSettingsHistoryReader $historyReader,
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
     * @return array<string, array<string, mixed>>
     */
    public function internalPayload(): array
    {
        $payload = [];

        foreach (OpsSettingsGroup::cases() as $group) {
            $settings = $this->settingsForGroup($group)->toArray();
            $payload[$group->value] = array_intersect_key(
                $settings,
                array_flip($group->internalProperties()),
            );
        }

        return $payload;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function compliancePayload(): array
    {
        $payload = [];

        foreach (OpsSettingsGroup::cases() as $group) {
            $settings = $this->settingsForGroup($group)->toArray();
            $payload[$group->value] = array_intersect_key(
                $settings,
                array_flip($group->complianceSensitiveProperties()),
            );
        }

        return $payload;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function missingRequiredFields(): array
    {
        if ($this->missingRequiredFieldsMemo !== null) {
            return $this->missingRequiredFieldsMemo;
        }

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

        return $this->missingRequiredFieldsMemo = $missing;
    }

    public function isComplete(): bool
    {
        return $this->missingRequiredFields() === [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function groupStatuses(): array
    {
        if ($this->groupStatusesMemo !== null) {
            return $this->groupStatusesMemo;
        }

        $missing = $this->missingRequiredFields();
        $statuses = [];
        $latestChanges = $this->historyReader->latestForGroups(OpsSettingsGroup::cases());

        foreach (OpsSettingsGroup::cases() as $group) {
            $approved = $group->approvedProperties();
            $settings = $this->settingsForGroup($group)->toArray();
            $filledCount = count(array_filter(
                $approved,
                static fn (string $property): bool => filled($settings[$property] ?? null),
            ));

            $statuses[$group->value] = [
                'label' => $group->label(),
                'description' => $group->description(),
                'missing_required' => $missing[$group->value] ?? [],
                'required_total' => count($group->requiredProperties()),
                'filled_total' => $filledCount,
                'approved_total' => count($approved),
                'completion_percent' => count($approved) === 0 ? 100 : (int) round(($filledCount / count($approved)) * 100),
                'status' => isset($missing[$group->value]) ? 'incomplete' : ($filledCount === 0 ? 'empty' : 'ready'),
                'latest_change' => $latestChanges[$group->value] ?? null,
            ];
        }

        return $this->groupStatusesMemo = $statuses;
    }

    public function completionPercent(): int
    {
        if ($this->completionPercentMemo !== null) {
            return $this->completionPercentMemo;
        }

        $statuses = $this->groupStatuses();

        if ($statuses === []) {
            return $this->completionPercentMemo = 100;
        }

        return $this->completionPercentMemo = (int) round(collect($statuses)->avg('completion_percent'));
    }

    /**
     * @return array<string, mixed>
     */
    public function identitySummary(): array
    {
        return [
            'name' => $this->identity()->name,
            'platform_label' => $this->identity()->platform_label,
            'display_name' => collect([$this->identity()->name, $this->identity()->platform_label])->filter()->implode(' · '),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function contactSummary(): array
    {
        return [
            'support_email' => $this->contact()->support_email,
            'noreply_email' => $this->contact()->noreply_email,
            'support_url' => $this->contact()->support_url,
            'country_code' => $this->contact()->country_code,
            'city_line' => collect([$this->contact()->postal_code, $this->contact()->city])->filter()->implode(' '),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function brandSummary(): array
    {
        return [
            'brand_name' => $this->brand()->brand_name,
            'brand_tagline' => $this->brand()->brand_tagline,
            'brand_claim' => $this->brand()->brand_claim,
            'primary_color' => $this->brand()->primary_color,
            'secondary_color' => $this->brand()->secondary_color,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function legalSummary(): array
    {
        return [
            'legal_entity_name' => $this->legal()->legal_entity_name,
            'registration_number' => $this->legal()->registration_number,
            'vat_id' => $this->legal()->vat_id,
            'privacy_contact_email' => $this->legal()->privacy_contact_email,
            'imprint_url' => $this->legal()->imprint_url,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function websiteDefaultsSummary(): array
    {
        return [
            'default_locale' => $this->websiteDefaults()->default_locale,
            'fallback_locale' => $this->websiteDefaults()->fallback_locale,
            'default_timezone' => $this->websiteDefaults()->default_timezone,
            'default_currency' => $this->websiteDefaults()->default_currency,
            'default_date_format' => $this->websiteDefaults()->default_date_format,
            'default_time_format' => $this->websiteDefaults()->default_time_format,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function exportSnapshot(): array
    {
        return [
            'exported_at' => now()->toIso8601String(),
            'completion_percent' => $this->completionPercent(),
            'groups' => $this->snapshot(),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function presetValues(string $preset): array
    {
        return OpsSettingsRegionPreset::values($preset);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function recentHistory(?OpsSettingsGroup $group = null, int $limit = 20): Collection
    {
        $memoKey = sprintf('%s:%d', $group?->value ?? 'all', $limit);

        if (isset($this->recentHistoryMemo[$memoKey])) {
            return $this->recentHistoryMemo[$memoKey];
        }

        return $this->recentHistoryMemo[$memoKey] = $this->historyReader->recent($group, $limit);
    }

    /**
     * Invalidates the package-owned cache for a specific settings group.
     * Called by UpdateOpsSettingsAction after a successful mutation.
     */
    public function invalidate(OpsSettingsGroup $group): void
    {
        unset($this->memo[$group->value]);
        $this->cacheRepository->forget($group->cacheKey());
        $this->flushDerivedMemo();
    }

    /**
     * Invalidates all package-owned settings caches.
     */
    public function invalidateAll(): void
    {
        foreach (OpsSettingsGroup::cases() as $group) {
            $this->invalidate($group);
        }

        $this->flushDerivedMemo();
    }

    private function flushDerivedMemo(): void
    {
        $this->missingRequiredFieldsMemo = null;
        $this->groupStatusesMemo = null;
        $this->completionPercentMemo = null;
        $this->recentHistoryMemo = [];
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
