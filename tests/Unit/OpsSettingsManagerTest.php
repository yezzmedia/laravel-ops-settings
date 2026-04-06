<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Settings;
use YezzMedia\OpsSettings\Settings\OperatorIdentitySettings;
use YezzMedia\OpsSettings\Settings\PlatformBrandSettings;
use YezzMedia\OpsSettings\Settings\PlatformContactSettings;
use YezzMedia\OpsSettings\Settings\PlatformLegalSettings;
use YezzMedia\OpsSettings\Settings\PlatformSocialSettings;
use YezzMedia\OpsSettings\Settings\PlatformWebsiteDefaultsSettings;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

beforeEach(function (): void {
    // Seed all settings groups with their baseline values.
    DB::table('settings')->insert([
        ['group' => 'identity', 'name' => 'name', 'locked' => false, 'payload' => json_encode('Test Operator')],
        ['group' => 'identity', 'name' => 'platform_label', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'support_email', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'contact_phone', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'address_line_1', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'address_line_2', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'postal_code', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'city', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'country_code', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'brand_name', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'brand_tagline', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'primary_color', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'secondary_color', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'logo_reference', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'facebook_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'instagram_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'linkedin_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'x_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'youtube_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'legal_entity_name', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'registration_number', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'vat_id', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'legal_notice_snippet', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'privacy_contact_email', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'website_defaults', 'name' => 'default_site_title_pattern', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'website_defaults', 'name' => 'default_footer_label', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'website_defaults', 'name' => 'default_support_label', 'locked' => false, 'payload' => json_encode(null)],
    ]);
});

it('returns the correct typed settings class for each group method', function (): void {
    $manager = app(OpsSettingsManager::class);

    expect($manager->identity())->toBeInstanceOf(OperatorIdentitySettings::class)
        ->and($manager->contact())->toBeInstanceOf(PlatformContactSettings::class)
        ->and($manager->brand())->toBeInstanceOf(PlatformBrandSettings::class)
        ->and($manager->social())->toBeInstanceOf(PlatformSocialSettings::class)
        ->and($manager->legal())->toBeInstanceOf(PlatformLegalSettings::class)
        ->and($manager->websiteDefaults())->toBeInstanceOf(PlatformWebsiteDefaultsSettings::class);
});

it('reads the seeded identity name from the database', function (): void {
    expect(app(OpsSettingsManager::class)->identity()->name)->toBe('Test Operator');
});

it('invalidates the cached entry for a specific group', function (): void {
    config()->set('ops-settings.cache.enabled', true);
    app()->forgetInstance(OpsSettingsManager::class);

    $manager = app(OpsSettingsManager::class);

    Cache::put(OpsSettingsGroup::Identity->cacheKey(), 'stale-value');
    $manager->invalidate(OpsSettingsGroup::Identity);

    expect(Cache::has(OpsSettingsGroup::Identity->cacheKey()))->toBeFalse();
});

it('invalidates all group caches at once', function (): void {
    config()->set('ops-settings.cache.enabled', true);
    app()->forgetInstance(OpsSettingsManager::class);
    $manager = app(OpsSettingsManager::class);

    foreach (OpsSettingsGroup::cases() as $group) {
        Cache::put($group->cacheKey(), 'stale');
    }

    $manager->invalidateAll();

    foreach (OpsSettingsGroup::cases() as $group) {
        expect(Cache::has($group->cacheKey()))->toBeFalse();
    }
});

it('hydrates typed settings instances from cached arrays', function (): void {
    config()->set('ops-settings.cache.enabled', true);
    app()->forgetInstance(OpsSettingsManager::class);

    Cache::put(OpsSettingsGroup::Identity->cacheKey(), [
        'name' => 'Cached Operator',
        'platform_label' => 'Cached Label',
    ]);

    $settings = app(OpsSettingsManager::class)->identity();

    expect($settings)->toBeInstanceOf(OperatorIdentitySettings::class)
        ->and($settings->name)->toBe('Cached Operator')
        ->and($settings->platform_label)->toBe('Cached Label');
});

it('replaces invalid cached payloads with fresh typed settings data', function (): void {
    config()->set('ops-settings.cache.enabled', true);
    app()->forgetInstance(OpsSettingsManager::class);

    Cache::put(OpsSettingsGroup::Identity->cacheKey(), new class extends Settings
    {
        public string $name = 'Broken';

        public ?string $platform_label = null;

        public static function group(): string
        {
            return 'identity';
        }
    });

    $settings = app(OpsSettingsManager::class)->identity();

    expect($settings)->toBeInstanceOf(OperatorIdentitySettings::class)
        ->and($settings->name)->toBe('Test Operator')
        ->and(Cache::get(OpsSettingsGroup::Identity->cacheKey()))->toBe([
            'name' => 'Test Operator',
            'platform_label' => null,
        ]);
});
