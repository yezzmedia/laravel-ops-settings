<?php

declare(strict_types=1);

use YezzMedia\OpsSettings\Settings\OperatorIdentitySettings;
use YezzMedia\OpsSettings\Settings\PlatformBrandSettings;
use YezzMedia\OpsSettings\Settings\PlatformContactSettings;
use YezzMedia\OpsSettings\Settings\PlatformLegalSettings;
use YezzMedia\OpsSettings\Settings\PlatformSocialSettings;
use YezzMedia\OpsSettings\Settings\PlatformWebsiteDefaultsSettings;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;

it('maps each group case to the correct settings class', function (): void {
    expect(OpsSettingsGroup::Identity->settingsClass())->toBe(OperatorIdentitySettings::class)
        ->and(OpsSettingsGroup::Contact->settingsClass())->toBe(PlatformContactSettings::class)
        ->and(OpsSettingsGroup::Brand->settingsClass())->toBe(PlatformBrandSettings::class)
        ->and(OpsSettingsGroup::Social->settingsClass())->toBe(PlatformSocialSettings::class)
        ->and(OpsSettingsGroup::Legal->settingsClass())->toBe(PlatformLegalSettings::class)
        ->and(OpsSettingsGroup::WebsiteDefaults->settingsClass())->toBe(PlatformWebsiteDefaultsSettings::class);
});

it('generates the correct cache key for each group', function (): void {
    expect(OpsSettingsGroup::Identity->cacheKey())->toBe('ops_settings.identity')
        ->and(OpsSettingsGroup::Contact->cacheKey())->toBe('ops_settings.contact')
        ->and(OpsSettingsGroup::Brand->cacheKey())->toBe('ops_settings.brand')
        ->and(OpsSettingsGroup::Social->cacheKey())->toBe('ops_settings.social')
        ->and(OpsSettingsGroup::Legal->cacheKey())->toBe('ops_settings.legal')
        ->and(OpsSettingsGroup::WebsiteDefaults->cacheKey())->toBe('ops_settings.website_defaults');
});

it('resolves a group from a valid string value', function (): void {
    expect(OpsSettingsGroup::fromValue('identity'))->toBe(OpsSettingsGroup::Identity)
        ->and(OpsSettingsGroup::fromValue('website_defaults'))->toBe(OpsSettingsGroup::WebsiteDefaults);
});

it('fails fast on unknown string group values', function (): void {
    expect(fn () => OpsSettingsGroup::fromValue('unknown_group'))
        ->toThrow(InvalidArgumentException::class, 'Unknown settings group [unknown_group].');
});

it('returns the approved properties for each settings group', function (OpsSettingsGroup $group, array $expected): void {
    expect($group->approvedProperties())->toBe($expected);
})->with([
    'identity' => [OpsSettingsGroup::Identity, ['name', 'platform_label']],
    'contact' => [OpsSettingsGroup::Contact, ['support_email', 'contact_phone', 'address_line_1', 'address_line_2', 'postal_code', 'city', 'country_code']],
    'brand' => [OpsSettingsGroup::Brand, ['brand_name', 'brand_tagline', 'primary_color', 'secondary_color', 'logo_reference']],
    'social' => [OpsSettingsGroup::Social, ['facebook_url', 'instagram_url', 'linkedin_url', 'x_url', 'youtube_url']],
    'legal' => [OpsSettingsGroup::Legal, ['legal_entity_name', 'registration_number', 'vat_id', 'legal_notice_snippet', 'privacy_contact_email']],
    'website_defaults' => [OpsSettingsGroup::WebsiteDefaults, ['default_site_title_pattern', 'default_footer_label', 'default_support_label']],
]);
