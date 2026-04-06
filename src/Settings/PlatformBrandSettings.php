<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Operator-owned global branding defaults used by downstream packages.
 */
class PlatformBrandSettings extends Settings
{
    public ?string $brand_name = null;

    public ?string $brand_tagline = null;

    public ?string $brand_claim = null;

    public ?string $default_email_from_name = null;

    /** Hex color string (e.g. #1a2b3c). */
    public ?string $primary_color = null;

    /** Hex color string (e.g. #1a2b3c). */
    public ?string $secondary_color = null;

    /**
     * Opaque internal asset reference string.
     * Arbitrary remote URLs are not approved for this field in V1.
     */
    public ?string $logo_reference = null;

    public ?string $favicon_reference = null;

    public ?string $icon_reference = null;

    public ?string $email_logo_reference = null;

    public static function group(): string
    {
        return 'brand';
    }
}
