<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        ['group' => 'contact', 'name' => 'noreply_email', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'contact_phone', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'contact_whatsapp', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'support_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'support_chat_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'support_hours', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'address_line_1', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'address_line_2', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'postal_code', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'city', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'contact', 'name' => 'country_code', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'brand_name', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'brand_tagline', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'brand_claim', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'default_email_from_name', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'primary_color', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'secondary_color', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'logo_reference', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'favicon_reference', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'icon_reference', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'brand', 'name' => 'email_logo_reference', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'facebook_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'instagram_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'linkedin_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'x_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'youtube_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'tiktok_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'threads_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'github_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'mastodon_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'social', 'name' => 'telegram_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'legal_entity_name', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'managing_director', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'registration_number', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'registration_court', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'vat_id', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'legal_notice_snippet', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'privacy_contact_email', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'imprint_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'privacy_policy_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'terms_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'legal', 'name' => 'cookie_policy_url', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'website_defaults', 'name' => 'default_site_title_pattern', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'website_defaults', 'name' => 'default_footer_label', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'website_defaults', 'name' => 'default_support_label', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'website_defaults', 'name' => 'default_support_cta_label', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'website_defaults', 'name' => 'default_reply_to_email', 'locked' => false, 'payload' => json_encode(null)],
        ['group' => 'website_defaults', 'name' => 'default_locale', 'locked' => false, 'payload' => json_encode('de')],
        ['group' => 'website_defaults', 'name' => 'fallback_locale', 'locked' => false, 'payload' => json_encode('en')],
        ['group' => 'website_defaults', 'name' => 'default_timezone', 'locked' => false, 'payload' => json_encode('Europe/Berlin')],
        ['group' => 'website_defaults', 'name' => 'default_currency', 'locked' => false, 'payload' => json_encode('EUR')],
        ['group' => 'website_defaults', 'name' => 'default_date_format', 'locked' => false, 'payload' => json_encode('d.m.Y')],
        ['group' => 'website_defaults', 'name' => 'default_time_format', 'locked' => false, 'payload' => json_encode('H:i')],
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

it('loads legacy settings stores without failing on newly added properties', function (): void {
    DB::table('settings')->where('group', 'contact')->whereIn('name', [
        'noreply_email',
        'contact_whatsapp',
        'support_url',
        'support_chat_url',
        'support_hours',
    ])->delete();

    $contact = app(OpsSettingsManager::class)->contact();

    expect($contact->support_email)->toBeNull()
        ->and($contact->noreply_email)->toBeNull()
        ->and($contact->contact_whatsapp)->toBeNull()
        ->and($contact->support_url)->toBeNull()
        ->and($contact->support_chat_url)->toBeNull()
        ->and($contact->support_hours)->toBeNull();
});

it('returns richer workspace payloads and summaries', function (): void {
    if (! Schema::hasTable('activity_log')) {
        Schema::create('activity_log', static function (Blueprint $table): void {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->string('event')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    DB::table('activity_log')->insert([
        'log_name' => 'ops_settings',
        'description' => 'ops.settings.updated',
        'event' => 'ops.settings.updated',
        'properties' => json_encode([
            'group' => 'identity',
            'changed_keys' => ['name'],
            'old_values' => ['name' => 'Old Operator'],
            'new_values' => ['name' => 'Test Operator'],
            'actor_id' => 5,
            'source' => 'test',
        ]),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $manager = app(OpsSettingsManager::class);

    expect($manager->groupStatuses()['identity']['label'])->toBe('Identity')
        ->and($manager->groupStatuses()['identity']['latest_change']['source'])->toBe('test')
        ->and($manager->completionPercent())->toBeGreaterThan(0)
        ->and($manager->identitySummary()['display_name'])->toBe('Test Operator')
        ->and($manager->websiteDefaultsSummary()['default_timezone'])->toBe('Europe/Berlin')
        ->and($manager->publicPayload()['brand'])->not->toHaveKey('logo_reference')
        ->and($manager->internalPayload()['brand'])->toHaveKey('logo_reference')
        ->and($manager->compliancePayload()['legal'])->toHaveKey('legal_entity_name')
        ->and($manager->exportSnapshot())->toHaveKeys(['exported_at', 'completion_percent', 'groups'])
        ->and($manager->presetValues('de')['website_defaults']['default_currency'])->toBe('EUR')
        ->and($manager->recentHistory()->first()['group'])->toBe('identity');
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

it('memoizes grouped statuses and batches latest history lookups', function (): void {
    if (! Schema::hasTable('activity_log')) {
        Schema::create('activity_log', static function (Blueprint $table): void {
            $table->id();
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject');
            $table->nullableMorphs('causer');
            $table->string('event')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    DB::table('activity_log')->insert([
        [
            'log_name' => 'ops_settings',
            'description' => 'ops.settings.updated',
            'event' => 'ops.settings.updated',
            'properties' => json_encode([
                'group' => 'identity',
                'changed_keys' => ['name'],
                'source' => 'test',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'log_name' => 'ops_settings',
            'description' => 'ops.settings.updated',
            'event' => 'ops.settings.updated',
            'properties' => json_encode([
                'group' => 'contact',
                'changed_keys' => ['support_email'],
                'source' => 'test',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $manager = app(OpsSettingsManager::class);
    $first = $manager->groupStatuses();
    $second = $manager->groupStatuses();

    $queries = collect(DB::getQueryLog())->pluck('query');

    expect($first['identity']['latest_change']['source'])->toBe('test')
        ->and($second['contact']['latest_change']['source'])->toBe('test')
        ->and($queries->filter(fn (string $query): bool => str_contains($query, 'activity_log'))->count())->toBe(2);
});
