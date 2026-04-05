<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Operator-owned global social defaults.
 * All URLs must be absolute HTTPS URLs when present.
 */
class PlatformSocialSettings extends Settings
{
    public ?string $facebook_url;

    public ?string $instagram_url;

    public ?string $linkedin_url;

    public ?string $x_url;

    public ?string $youtube_url;

    public static function group(): string
    {
        return 'social';
    }
}
