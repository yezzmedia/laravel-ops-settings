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
    public ?string $default_site_title_pattern;

    public ?string $default_footer_label;

    public ?string $default_support_label;

    public static function group(): string
    {
        return 'website_defaults';
    }
}
