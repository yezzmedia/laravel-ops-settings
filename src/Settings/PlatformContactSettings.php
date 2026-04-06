<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Operator-owned global contact defaults.
 */
class PlatformContactSettings extends Settings
{
    public ?string $support_email = null;

    public ?string $noreply_email = null;

    public ?string $contact_phone = null;

    public ?string $contact_whatsapp = null;

    public ?string $support_url = null;

    public ?string $support_chat_url = null;

    public ?string $support_hours = null;

    public ?string $address_line_1 = null;

    public ?string $address_line_2 = null;

    public ?string $postal_code = null;

    public ?string $city = null;

    /** ISO 3166-1 alpha-2 country code. */
    public ?string $country_code = null;

    public static function group(): string
    {
        return 'contact';
    }
}
