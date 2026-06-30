# Changelog

All notable changes to `yezzmedia/laravel-ops-settings` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- optional persisted audit configuration through `ops-settings.audit.driver`
- package-owned audit writer contract and implementations:
  - `OpsSettingsAuditWriter`
  - `NullOpsSettingsAuditWriter`
  - `ActivityLogOpsSettingsAuditWriter`
- `OpsSettingsAuditListener` for bridging `OpsSettingsUpdated` into the configured audit writer
- `ConfigureOpsSettingsAuditInstallStep` for enabling persisted ops-settings audit through the foundation installer
- doctor diagnostics:
  - `OpsSettingsAuditConfiguredCheck`
  - `OpsSettingsStoreReadyCheck`
- central `OpsSettingsPage` workspace with vertical group tabs, readiness overview, status badges, preset application, JSON import/export helpers, and recent history presentation
- expanded grouped settings surface for contact, brand, social, legal, and website-default defaults
- curated searchable selects for locale, timezone, currency, date format, time format, and country code inside the operator workspace
- region presets for `de`, `ch`, `at`, and `us`
- manager helpers for grouped status summaries, public/internal/compliance payload partitions, exports, presets, and recent history access
- doctor diagnostics:
  - `OpsSettingsCompletenessCheck`
  - `OpsSettingsConsistencyCheck`
- `OpsSettingsHistoryReader` and `OpsSettingsValidator` for runtime-safe history access and normalized mutation validation
- regression coverage for legacy settings-store compatibility, workspace payload helpers, schema select behavior, and recent-changes workspace rendering
- security-governance declarations through foundation:
  - `ops-settings.request.auth.password-confirmation`
  - `ops-settings.auth.password-confirmation`
- package-owned password confirmation workflow for destructive ops-settings mutations
- snapshot export and import audit event definitions:
  - `ops.settings.snapshot_exported`
  - `ops.settings.snapshot_imported`

### Changed

- `OpsSettingsUpdated` now carries `oldValues` and `newValues` snapshots for changed keys
- the package audit event definition now includes `old_values` and `new_values` context keys for persisted audit backends
- package config publishing now registers `config/ops-settings.php` under the explicit `ops-settings-config` publish tag
- settings mutations now normalize and validate approved values before persistence, including curated locale/currency/date/time defaults and stricter URL/email/color rules
- recent workspace history is now presented as a compact table-style audit overview for operators
- expanded settings classes now stay compatible with legacy stores by defaulting missing properties and backfilling missing approved keys on save
- destructive workspace mutations now require password confirmation for a bounded session timeout before save, preset apply, and snapshot import flows proceed
- settings-store readiness now reuses the established migrations-table state across one evaluation flow to avoid duplicate readiness checks

### Documentation

- documented the optional audit configuration, doctor checks, expanded event payload, and the implemented `website:install --configure-audit --audit-package=*` flow in the package README
- corrected the manual config publishing examples to use the real `ops-settings-config` tag
- documented the current workspace UX, expanded grouped field surface, stronger validation rules, runtime payload helpers, region presets, and four registered doctor checks in the package README
- documented the password-confirmation timeout config, security-governance declarations, destructive-action protection, and corrected doctor-check keys and statuses in the package README

## [0.2.0] - 2026-06-30

### Changed

- Bumped minimum `yezzmedia/laravel-foundation` dependency to `^0.2`

## [0.1.0] - 2026-04-05

### Added

- Six settings groups backed by `spatie/laravel-settings` v3:
  - `OperatorIdentitySettings` — operator name and platform label
  - `PlatformContactSettings` — support email, phone, address fields
  - `PlatformBrandSettings` — brand name, tagline, hex colors, logo reference
  - `PlatformSocialSettings` — social media profile URLs
  - `PlatformLegalSettings` — legal entity, VAT, registration number, legal notice snippet, privacy contact
  - `PlatformWebsiteDefaultsSettings` — site title pattern, footer label, support label
- `OpsSettingsManager` — cache-aware, per-request memoized read API for all settings groups
- `UpdateOpsSettingsAction` — guarded mutation boundary with attribute allowlist, cache invalidation, and event dispatch
- `OpsSettingsUpdated` event dispatched after every successful settings update
- `OpsSettingsGroup` enum mapping groups to settings classes, approved properties, and cache keys
- `OpsSettingsPlatformPackage` — foundation descriptor registering permissions, audit events, and install steps
- Two permissions registered via `yezzmedia/laravel-foundation`: `ops.settings.view`, `ops.settings.manage`
- Audit event definition `ops.settings.updated` with severity `warning`
- Three install steps: publish migrations, verify settings store readiness, seed defaults
- `OpsSettingsDefaultsSeeder` for populating empty string defaults on first install
- `OpsSettingsStoreSetup` doctor check verifying the settings table is present and migrated
- `PublishOpsSettingsMigrationsInstallStep` — publishes the six settings migrations
- `EnsureOpsSettingsStoreReadyInstallStep` — verifies the settings store is operational
- `SeedOpsSettingsDefaultsInstallStep` — seeds default values during install
- `OpsSettingsTestCase` base class for consuming package test suites
- 32 tests covering bootstrap, install steps, manager, action, event, and settings groups
- `config/ops-settings.php` with cache and defaults configuration

[Unreleased]: https://github.com/yezzmedia/laravel-ops-settings/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/yezzmedia/laravel-ops-settings/releases/tag/v0.1.0
