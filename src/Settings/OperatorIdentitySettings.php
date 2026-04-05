<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Operator-owned organization and platform identity defaults.
 */
class OperatorIdentitySettings extends Settings
{
    /** Operator display name used for platform presentation. */
    public string $name;

    /** Optional platform label used in sub-brand or multi-tenant presentation. */
    public ?string $platform_label;

    public static function group(): string
    {
        return 'identity';
    }
}
