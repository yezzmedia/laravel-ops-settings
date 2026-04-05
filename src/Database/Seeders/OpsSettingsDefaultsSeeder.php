<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Database\Seeders;

use Spatie\LaravelSettings\SettingsRepositories\SettingsRepository;
use YezzMedia\OpsSettings\Settings\OperatorIdentitySettings;
use YezzMedia\OpsSettings\Settings\PlatformBrandSettings;
use YezzMedia\OpsSettings\Settings\PlatformContactSettings;
use YezzMedia\OpsSettings\Settings\PlatformLegalSettings;
use YezzMedia\OpsSettings\Settings\PlatformSocialSettings;
use YezzMedia\OpsSettings\Settings\PlatformWebsiteDefaultsSettings;

/**
 * Seeds required baseline ops settings defaults.
 *
 * Idempotent: skips already initialized operator-managed values.
 * Must not silently overwrite existing values.
 */
class OpsSettingsDefaultsSeeder
{
    public function __construct(private readonly SettingsRepository $repository) {}

    public function run(): void
    {
        $this->seedIfMissing(OperatorIdentitySettings::group(), 'name', '');
        $this->seedIfMissing(OperatorIdentitySettings::group(), 'platform_label', null);

        $this->seedIfMissing(PlatformContactSettings::group(), 'support_email', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'contact_phone', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'address_line_1', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'address_line_2', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'postal_code', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'city', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'country_code', null);

        $this->seedIfMissing(PlatformBrandSettings::group(), 'brand_name', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'brand_tagline', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'primary_color', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'secondary_color', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'logo_reference', null);

        $this->seedIfMissing(PlatformSocialSettings::group(), 'facebook_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'instagram_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'linkedin_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'x_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'youtube_url', null);

        $this->seedIfMissing(PlatformLegalSettings::group(), 'legal_entity_name', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'registration_number', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'vat_id', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'legal_notice_snippet', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'privacy_contact_email', null);

        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_site_title_pattern', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_footer_label', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_support_label', null);
    }

    private function seedIfMissing(string $group, string $name, mixed $default): void
    {
        if ($this->repository->getPropertiesInGroup($group)->has($name)) {
            return;
        }

        $this->repository->createProperty($group, $name, $default);
    }
}
