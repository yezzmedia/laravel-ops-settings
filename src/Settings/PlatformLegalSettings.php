<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Operator-owned legal metadata and approved legal default snippets.
 */
class PlatformLegalSettings extends Settings
{
    /** Formal legal entity name. Do not duplicate in OperatorIdentitySettings. */
    public ?string $legal_entity_name;

    public ?string $registration_number;

    public ?string $vat_id;

    /** Plaintext or Markdown only. Raw HTML is not approved in V1. */
    public ?string $legal_notice_snippet;

    public ?string $privacy_contact_email;

    public static function group(): string
    {
        return 'legal';
    }
}
