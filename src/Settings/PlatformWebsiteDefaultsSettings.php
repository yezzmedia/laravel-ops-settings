<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Narrow global fallback values for downstream packages.
 * Must remain a narrow fallback group, not a generic settings bucket.
 */
class PlatformWebsiteDefaultsSettings extends Settings
{
    public ?string $default_site_title_pattern = null;

    public ?string $default_footer_label = null;

    public ?string $default_support_label = null;

    public ?string $default_support_cta_label = null;

    public ?string $default_reply_to_email = null;

    public ?string $default_locale = null;

    public ?string $fallback_locale = null;

    public ?string $default_timezone = null;

    public ?string $default_currency = null;

    public ?string $default_date_format = null;

    public ?string $default_time_format = null;

    public static function group(): string
    {
        return 'website_defaults';
    }
}
