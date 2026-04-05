<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Operator-owned global branding defaults used by downstream packages.
 */
class PlatformBrandSettings extends Settings
{
    public ?string $brand_name;

    public ?string $brand_tagline;

    /** Hex color string (e.g. #1a2b3c). */
    public ?string $primary_color;

    /** Hex color string (e.g. #1a2b3c). */
    public ?string $secondary_color;

    /**
     * Opaque internal asset reference string.
     * Arbitrary remote URLs are not approved for this field in V1.
     */
    public ?string $logo_reference;

    public static function group(): string
    {
        return 'brand';
    }
}
