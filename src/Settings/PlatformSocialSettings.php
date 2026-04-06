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
    public ?string $facebook_url = null;

    public ?string $instagram_url = null;

    public ?string $linkedin_url = null;

    public ?string $x_url = null;

    public ?string $youtube_url = null;

    public ?string $tiktok_url = null;

    public ?string $threads_url = null;

    public ?string $github_url = null;

    public ?string $mastodon_url = null;

    public ?string $telegram_url = null;

    public static function group(): string
    {
        return 'social';
    }
}
