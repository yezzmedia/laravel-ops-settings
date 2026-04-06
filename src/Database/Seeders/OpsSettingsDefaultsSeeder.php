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
        $this->seedIfMissing(PlatformContactSettings::group(), 'noreply_email', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'contact_phone', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'contact_whatsapp', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'support_url', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'support_chat_url', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'support_hours', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'address_line_1', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'address_line_2', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'postal_code', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'city', null);
        $this->seedIfMissing(PlatformContactSettings::group(), 'country_code', null);

        $this->seedIfMissing(PlatformBrandSettings::group(), 'brand_name', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'brand_tagline', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'brand_claim', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'default_email_from_name', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'primary_color', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'secondary_color', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'logo_reference', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'favicon_reference', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'icon_reference', null);
        $this->seedIfMissing(PlatformBrandSettings::group(), 'email_logo_reference', null);

        $this->seedIfMissing(PlatformSocialSettings::group(), 'facebook_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'instagram_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'linkedin_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'x_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'youtube_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'tiktok_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'threads_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'github_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'mastodon_url', null);
        $this->seedIfMissing(PlatformSocialSettings::group(), 'telegram_url', null);

        $this->seedIfMissing(PlatformLegalSettings::group(), 'legal_entity_name', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'managing_director', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'registration_number', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'registration_court', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'vat_id', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'legal_notice_snippet', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'privacy_contact_email', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'imprint_url', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'privacy_policy_url', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'terms_url', null);
        $this->seedIfMissing(PlatformLegalSettings::group(), 'cookie_policy_url', null);

        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_site_title_pattern', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_footer_label', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_support_label', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_support_cta_label', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_reply_to_email', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_locale', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'fallback_locale', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_timezone', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_currency', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_date_format', null);
        $this->seedIfMissing(PlatformWebsiteDefaultsSettings::group(), 'default_time_format', null);
    }

    private function seedIfMissing(string $group, string $name, mixed $default): void
    {
        if ($this->repository->getPropertiesInGroup($group)->has($name)) {
            return;
        }

        $this->repository->createProperty($group, $name, $default);
    }
}
