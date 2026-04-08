# laravel-ops-settings

Operator-managed global platform settings for the Yezz Media Laravel website platform.

Provides six structured settings groups (identity, contact, brand, social, legal, website defaults) backed by [`spatie/laravel-settings`](https://github.com/spatie/laravel-settings) v3, with a cache-aware manager, guarded mutations, audit events, a foundation-integrated install flow, and a Filament-powered operator workspace.

## Requirements

| Dependency | Version |
|---|---|
| PHP | `^8.4` |
| Laravel | `^13.0` |
| yezzmedia/laravel-foundation | `^0.1` |
| spatie/laravel-settings | `^3.0` |

Optional:

- `spatie/laravel-activitylog ^5.0` for persisted ops-settings audit records

## Installation

```bash
composer require yezzmedia/laravel-ops-settings
```

The service provider is registered automatically via Laravel's package discovery.

### Install flow

Run the foundation install command to publish migrations, verify the settings store, and seed defaults:

```bash
php artisan website:install
```

Or step through manually:

```bash
# 1. Publish the settings migrations
php artisan vendor:publish --tag=laravel-ops-settings-migrations

# 2. Run migrations
php artisan migrate

# 3. Seed defaults (optional)
php artisan db:seed --class="YezzMedia\OpsSettings\Database\Seeders\OpsSettingsDefaultsSeeder"
```

### Audit persistence

Persisted ops-settings audit is optional.

Manual setup:

```bash
composer require spatie/laravel-activitylog
php artisan vendor:publish --tag=ops-settings-config
```

Then set `ops-settings.audit.driver` to `activitylog`.

Central install flow:

```bash
php artisan website:install --configure-audit --audit-package=yezzmedia/laravel-ops-settings
php artisan website:install --configure-audit --audit-package=all
```

## Operator workspace

When the consuming application exposes the ops panel, the package registers a central `OpsSettingsPage` workspace.

The workspace includes:

- one visible tabbed workspace page for all six settings groups
- per-tab save boundaries through `UpdateOpsSettingsAction`
- readiness snapshot and grouped completion badges
- workspace overview cards with missing-required-field summaries
- recent-changes audit table sourced from persisted ops-settings activity when available
- curated region presets for `de`, `ch`, `at`, and `us`
- JSON export/import helpers for grouped snapshot review
- searchable selects for locale, timezone, currency, date format, time format, and country code

The page keeps the legacy single-group pages registered as hidden deep-link compatibility pages, while the visible navigation surface now points at the central workspace.

### Password confirmation workflow

Destructive mutations inside the central workspace require explicit password confirmation.

The current protected paths include:

- group save actions through `saveIdentity()`, `saveContact()`, `saveBrand()`, `saveSocial()`, `saveLegal()`, and `saveWebsiteDefaults()`
- preset application through `applyPreset`
- snapshot import through `importSnapshot`

`confirmPassword()` stores a session-scoped confirmation timestamp and honors `ops-settings.security.password_confirmation.timeout`.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=ops-settings-config
```

`config/ops-settings.php`:

```php
return [
    'cache' => [
        'enabled' => true,   // Toggle package-owned settings cache
        'store'   => null,   // Cache store to use (null = default)
    ],
    'audit' => [
        'driver' => null,    // null or 'activitylog'
    ],
    'defaults' => [
        'seed_on_install' => true, // Seed defaults during install flow
    ],

    'workspace' => [
        'history_limit' => 20,                 // Maximum recent audit rows shown in the workspace
        'presets' => ['de', 'ch', 'at', 'us'], // Curated region presets available in the workspace
    ],

    'security' => [
        'password_confirmation' => [
            'timeout' => 900, // Seconds that one password confirmation stays valid in the workspace session
        ],
    ],
];
```

When `audit.driver` is `null`, the package emits runtime audit events without persisting them.
When `audit.driver` is `activitylog`, the package uses the activitylog-backed audit writer.

## Settings Groups

| Group | Class | `::group()` key | Description |
|---|---|---|---|
| Identity | `OperatorIdentitySettings` | `identity` | Operator name and platform label |
| Contact | `PlatformContactSettings` | `contact` | Support channels, outbound email, URLs, and postal address |
| Brand | `PlatformBrandSettings` | `brand` | Brand copy, sender defaults, colors, and internal asset references |
| Social | `PlatformSocialSettings` | `social` | Official public social and community profile URLs |
| Legal | `PlatformLegalSettings` | `legal` | Legal entity, registration, compliance URLs, and notice content |
| Website Defaults | `PlatformWebsiteDefaultsSettings` | `website_defaults` | Reusable locale, timezone, currency, formatting, and copy defaults |

### Properties

**OperatorIdentitySettings**

| Property | Type | Description |
|---|---|---|
| `name` | `string` | Operator display name |
| `platform_label` | `?string` | Sub-brand or multi-tenant label |

**PlatformContactSettings**

| Property | Type | Description |
|---|---|---|
| `support_email` | `?string` | Primary support email address |
| `noreply_email` | `?string` | Outbound-only no-reply address |
| `contact_phone` | `?string` | Contact phone number |
| `contact_whatsapp` | `?string` | WhatsApp-compatible support number |
| `support_url` | `?string` | Canonical support or help-center URL |
| `support_chat_url` | `?string` | Canonical chat entry URL |
| `support_hours` | `?string` | Operator-authored support-hours copy |
| `address_line_1` | `?string` | Address line 1 |
| `address_line_2` | `?string` | Address line 2 |
| `postal_code` | `?string` | Postal code |
| `city` | `?string` | City |
| `country_code` | `?string` | ISO 3166-1 alpha-2 country code |

**PlatformBrandSettings**

| Property | Type | Description |
|---|---|---|
| `brand_name` | `?string` | Brand display name |
| `brand_tagline` | `?string` | Short tagline |
| `brand_claim` | `?string` | Secondary brand claim or promise |
| `default_email_from_name` | `?string` | Default sender display name |
| `primary_color` | `?string` | Hex color (e.g. `#1a2b3c`) |
| `secondary_color` | `?string` | Hex color (e.g. `#1a2b3c`) |
| `logo_reference` | `?string` | Opaque internal asset reference |
| `favicon_reference` | `?string` | Internal favicon asset reference |
| `icon_reference` | `?string` | Internal icon-mark asset reference |
| `email_logo_reference` | `?string` | Internal email-logo asset reference |

**PlatformSocialSettings**

| Property | Type | Description |
|---|---|---|
| `facebook_url` | `?string` | Absolute HTTPS URL |
| `instagram_url` | `?string` | Absolute HTTPS URL |
| `linkedin_url` | `?string` | Absolute HTTPS URL |
| `x_url` | `?string` | Absolute HTTPS URL |
| `youtube_url` | `?string` | Absolute HTTPS URL |
| `tiktok_url` | `?string` | Absolute HTTPS URL |
| `threads_url` | `?string` | Absolute HTTPS URL |
| `github_url` | `?string` | Absolute HTTPS URL |
| `mastodon_url` | `?string` | Absolute HTTPS URL |
| `telegram_url` | `?string` | Absolute HTTPS URL |

**PlatformLegalSettings**

| Property | Type | Description |
|---|---|---|
| `legal_entity_name` | `?string` | Formal legal entity name |
| `managing_director` | `?string` | Responsible managing director or executive |
| `registration_number` | `?string` | Company registration number |
| `registration_court` | `?string` | Registration court or authority |
| `vat_id` | `?string` | VAT identification number |
| `legal_notice_snippet` | `?string` | Plaintext or Markdown (no raw HTML in V1) |
| `privacy_contact_email` | `?string` | Privacy / GDPR contact email |
| `imprint_url` | `?string` | Canonical imprint URL |
| `privacy_policy_url` | `?string` | Canonical privacy policy URL |
| `terms_url` | `?string` | Canonical terms URL |
| `cookie_policy_url` | `?string` | Canonical cookie policy URL |

**PlatformWebsiteDefaultsSettings**

| Property | Type | Description |
|---|---|---|
| `default_site_title_pattern` | `?string` | Site title pattern for downstream packages |
| `default_footer_label` | `?string` | Global footer label fallback |
| `default_support_label` | `?string` | Global support label fallback |
| `default_support_cta_label` | `?string` | Global support CTA label fallback |
| `default_reply_to_email` | `?string` | Reply-to fallback for downstream mail usage |
| `default_locale` | `?string` | Default locale code |
| `fallback_locale` | `?string` | Fallback locale code |
| `default_timezone` | `?string` | Default timezone identifier |
| `default_currency` | `?string` | Default ISO 4217 currency code |
| `default_date_format` | `?string` | Default curated date format token |
| `default_time_format` | `?string` | Default curated time format token |

## Validation and normalization

`UpdateOpsSettingsAction` validates and normalizes approved group attributes before persistence.

Current validation highlights include:

- trimmed string input with empty-string-to-`null` normalization
- uppercase normalization for `country_code` and `default_currency`
- curated option validation for locale, currency, date format, and time format
- `timezone:all` validation for `default_timezone`
- URL validation for support, social, and legal page links
- hex color validation for `primary_color` and `secondary_color`
- enforcement that `noreply_email` must differ from `support_email`

Passing an approved key with an invalid value throws `InvalidArgumentException` with the first validation error message.

## Reading Settings

Prefer `OpsSettingsManager` over direct storage access. It provides per-request memoization and optional cache-forever reads:

```php
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

$settings = app(OpsSettingsManager::class);

$settings->identity()->name;
$settings->contact()->support_email;
$settings->brand()->primary_color;
$settings->social()->instagram_url;
$settings->legal()->vat_id;
$settings->websiteDefaults()->default_site_title_pattern;

$settings->completionPercent();
$settings->groupStatuses();
$settings->identitySummary();
$settings->contactSummary();
$settings->websiteDefaultsSummary();
$settings->publicPayload();
$settings->internalPayload();
$settings->compliancePayload();
$settings->exportSnapshot();
$settings->presetValues('de');
$settings->recentHistory();
```

Additional grouped helpers include:

- `missingRequiredFields()` and `isComplete()` for workspace and ops readiness
- `groupStatuses()` for per-group completion metadata and latest audit entry lookup
- summary helpers for identity, contact, brand, legal, and website-default output
- `exportSnapshot()` for JSON-friendly grouped exports
- `recentHistory()` for normalized persisted audit rows when activity history is available

## Updating Settings

Use `UpdateOpsSettingsAction` for all mutations. It validates attributes against an approved property list, saves the settings, invalidates the cache, and dispatches `OpsSettingsUpdated`.

```php
use YezzMedia\OpsSettings\Actions\UpdateOpsSettingsAction;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;

app(UpdateOpsSettingsAction::class)->execute(
    group: OpsSettingsGroup::Identity,
    attributes: ['name' => 'Acme Corp'],
    actorId: auth()->id(),
    context: [],
    source: 'admin-panel',
);
```

Passing an attribute that is not an approved property of the group throws `InvalidArgumentException`.

The action also backfills missing legacy keys before saving expanded settings groups, so older installations can safely adopt newer settings properties incrementally.

Authorization enforcement is the caller's responsibility.

## Events

### `OpsSettingsUpdated`

Dispatched after every successful `UpdateOpsSettingsAction::execute()` call.

| Property | Type | Description |
|---|---|---|
| `group` | `OpsSettingsGroup` | The settings group that was updated |
| `changedKeys` | `array<int, string>` | Attribute keys that were changed |
| `actorId` | `int\|string\|null` | ID of the actor who triggered the update |
| `oldValues` | `array<string, mixed>` | Previous values for the changed keys |
| `newValues` | `array<string, mixed>` | Persisted values after the update |
| `context` | `array<string, mixed>` | Arbitrary caller-provided context |
| `source` | `?string` | String identifying the update source |

## Audit integration

The package defines the `ops.settings.updated` audit event through foundation metadata and writes persisted audit only when explicitly configured.

It also defines security-governance metadata through foundation:

- security request: `ops-settings.request.auth.password-confirmation`
- security requirement: `ops-settings.auth.password-confirmation`

The package keeps enforcement package-owned: `yezzmedia/laravel-ops-security` verifies that the confirmation workflow exists, but the actual confirmation UX remains on the ops-settings page.

Audit writer behavior:

- `ops-settings.audit.driver=null`: use `NullOpsSettingsAuditWriter`
- `ops-settings.audit.driver=activitylog`: use `ActivityLogOpsSettingsAuditWriter`

If `activitylog` is configured but `spatie/laravel-activitylog` is missing, the package fails explicitly during binding.

The foundation audit installer updates package config only. It does not run the ordinary ops-settings install flow, migrations, or default seeding.

## Doctor checks

The package registers four doctor checks through foundation:

- `audit_configured`
  - `skipped` when the host ops audit provider is not configured in the current environment
  - `passed` when `ops-settings.audit.driver=activitylog`
  - `warning` when persisted audit is intentionally disabled
  - `failed` when an unsupported audit driver is configured
- `settings_completeness`
  - `passed` when all required baseline ops settings fields are filled
  - `warning` when required fields are still missing
- `settings_consistency`
  - `passed` when core defaults are internally consistent
  - `warning` when conflicting defaults are detected, such as matching support/no-reply emails or identical default and fallback locales
- `settings_store_ready`
  - `passed` when the settings store is present and all published ops-settings migrations are applied
  - `failed` when the settings table or required migrations are missing
  - `warning` when the settings table exists but published ops-settings migrations are still pending

## Permissions

| Name | Description |
|---|---|
| `ops.settings.view` | Read operator-managed global platform settings |
| `ops.settings.manage` | Mutate operator-managed global platform settings |

Permissions are registered via `OpsSettingsPlatformPackage` and synchronized by `yezzmedia/laravel-access`.

Operators can opt this package into persisted audit through `website:install --configure-audit --audit-package=yezzmedia/laravel-ops-settings`.

## Runtime partitions

`OpsSettingsGroup` now classifies approved properties into runtime-friendly partitions so downstream consumers can choose the right payload surface:

- public properties for broadly reusable, safe presentation defaults
- internal properties for package-owned asset references and similar internal wiring
- compliance-sensitive properties for legal and regulated data handling

Use `publicPayload()`, `internalPayload()`, and `compliancePayload()` on `OpsSettingsManager` instead of building these partitions manually.

## Testing

This package ships a `OpsSettingsTestCase` for use in consuming packages:

```php
use YezzMedia\OpsSettings\Tests\OpsSettingsTestCase;

uses(OpsSettingsTestCase::class);
```

Run the package test suite:

```bash
composer test
```

## License

MIT — see [LICENSE](LICENSE) for details.
