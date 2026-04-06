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
    public ?string $legal_entity_name = null;

    public ?string $managing_director = null;

    public ?string $registration_number = null;

    public ?string $registration_court = null;

    public ?string $vat_id = null;

    /** Plaintext or Markdown only. Raw HTML is not approved in V1. */
    public ?string $legal_notice_snippet = null;

    public ?string $privacy_contact_email = null;

    public ?string $imprint_url = null;

    public ?string $privacy_policy_url = null;

    public ?string $terms_url = null;

    public ?string $cookie_policy_url = null;

    public static function group(): string
    {
        return 'legal';
    }
}
