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

### Changed

- `OpsSettingsUpdated` now carries `oldValues` and `newValues` snapshots for changed keys
- the package audit event definition now includes `old_values` and `new_values` context keys for persisted audit backends

### Documentation

- documented the optional audit configuration, doctor checks, expanded event payload, and the implemented `website:install --configure-audit --audit-package=*` flow in the package README

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
