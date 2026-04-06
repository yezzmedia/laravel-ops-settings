# laravel-ops-settings

Operator-managed global platform settings for the Yezz Media Laravel website platform.

Provides six structured settings groups (identity, contact, brand, social, legal, website defaults) backed by [`spatie/laravel-settings`](https://github.com/spatie/laravel-settings) v3, with a cache-aware manager, a guarded mutation action, audit events, and a foundation-integrated install flow.

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
];
```

When `audit.driver` is `null`, the package emits runtime audit events without persisting them.
When `audit.driver` is `activitylog`, the package uses the activitylog-backed audit writer.

## Settings Groups

| Group | Class | `::group()` key | Description |
|---|---|---|---|
| Identity | `OperatorIdentitySettings` | `identity` | Operator name and platform label |
| Contact | `PlatformContactSettings` | `contact` | Support email, phone, address |
| Brand | `PlatformBrandSettings` | `brand` | Brand name, tagline, colors, logo reference |
| Social | `PlatformSocialSettings` | `social` | Social media profile URLs |
| Legal | `PlatformLegalSettings` | `legal` | Legal entity, VAT, legal notice snippet |
| Website Defaults | `PlatformWebsiteDefaultsSettings` | `website_defaults` | Site title pattern, footer label, support label |

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
| `contact_phone` | `?string` | Contact phone number |
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
| `primary_color` | `?string` | Hex color (e.g. `#1a2b3c`) |
| `secondary_color` | `?string` | Hex color (e.g. `#1a2b3c`) |
| `logo_reference` | `?string` | Opaque internal asset reference |

**PlatformSocialSettings**

| Property | Type | Description |
|---|---|---|
| `facebook_url` | `?string` | Absolute HTTPS URL |
| `instagram_url` | `?string` | Absolute HTTPS URL |
| `linkedin_url` | `?string` | Absolute HTTPS URL |
| `x_url` | `?string` | Absolute HTTPS URL |
| `youtube_url` | `?string` | Absolute HTTPS URL |

**PlatformLegalSettings**

| Property | Type | Description |
|---|---|---|
| `legal_entity_name` | `?string` | Formal legal entity name |
| `registration_number` | `?string` | Company registration number |
| `vat_id` | `?string` | VAT identification number |
| `legal_notice_snippet` | `?string` | Plaintext or Markdown (no raw HTML in V1) |
| `privacy_contact_email` | `?string` | Privacy / GDPR contact email |

**PlatformWebsiteDefaultsSettings**

| Property | Type | Description |
|---|---|---|
| `default_site_title_pattern` | `?string` | Site title pattern for downstream packages |
| `default_footer_label` | `?string` | Global footer label fallback |
| `default_support_label` | `?string` | Global support label fallback |

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
```

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

Audit writer behavior:

- `ops-settings.audit.driver=null`: use `NullOpsSettingsAuditWriter`
- `ops-settings.audit.driver=activitylog`: use `ActivityLogOpsSettingsAuditWriter`

If `activitylog` is configured but `spatie/laravel-activitylog` is missing, the package fails explicitly during binding.

The foundation audit installer updates package config only. It does not run the ordinary ops-settings install flow, migrations, or default seeding.

## Doctor checks

The package registers two doctor checks through foundation:

- `ops_settings_audit_configured`
  - `passed` when `ops-settings.audit.driver=activitylog`
  - `warning` when persisted audit is intentionally disabled
  - `failed` when an unsupported audit driver is configured
- `ops_settings_store_ready`
  - `passed` when the settings store is present and all published ops-settings migrations are applied
  - `failed` when the settings table or required migrations are missing

## Permissions

| Name | Description |
|---|---|
| `ops.settings.view` | Read operator-managed global platform settings |
| `ops.settings.manage` | Mutate operator-managed global platform settings |

Permissions are registered via `OpsSettingsPlatformPackage` and synchronized by `yezzmedia/laravel-access`.

Operators can opt this package into persisted audit through `website:install --configure-audit --audit-package=yezzmedia/laravel-ops-settings`.

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
