<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Operator-owned global contact defaults.
 */
class PlatformContactSettings extends Settings
{
    public ?string $support_email;

    public ?string $contact_phone;

    public ?string $address_line_1;

    public ?string $address_line_2;

    public ?string $postal_code;

    public ?string $city;

    /** ISO 3166-1 alpha-2 country code. */
    public ?string $country_code;

    public static function group(): string
    {
        return 'contact';
    }
}
