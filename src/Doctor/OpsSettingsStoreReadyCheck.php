<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Doctor;

use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorCheck;
use YezzMedia\OpsSettings\Support\OpsSettingsStoreSetup;

final readonly class OpsSettingsStoreReadyCheck implements DoctorCheck
{
    private const KEY = 'settings_store_ready';

    private const PACKAGE = 'yezzmedia/laravel-ops-settings';

    public function __construct(
        private OpsSettingsStoreSetup $storeSetup,
    ) {}

    public function key(): string
    {
        return self::KEY;
    }

    public function package(): string
    {
        return self::PACKAGE;
    }

    public function run(): DoctorResult
    {
        if ($this->storeSetup->storeReady()) {
            return $this->result(
                status: 'passed',
                message: 'The ops settings store is ready for runtime use.',
                context: [
                    'settings_table_exists' => true,
                    'pending_migrations' => false,
                ],
            );
        }

        if (! $this->storeSetup->settingsTableExists()) {
            return $this->result(
                status: 'failed',
                message: 'The ops settings store is not ready because the settings table is missing.',
                isBlocking: true,
                context: [
                    'settings_table_exists' => false,
                    'migrations_published' => $this->storeSetup->migrationsPublished(),
                ],
            );
        }

        return $this->result(
            status: 'warning',
            message: 'The ops settings store has pending migrations and is not fully ready.',
            context: [
                'settings_table_exists' => true,
                'migrations_published' => $this->storeSetup->migrationsPublished(),
                'pending_migrations' => $this->storeSetup->hasPendingMigrations(),
            ],
        );
    }

    /**
     * @param  array<string, mixed>|null  $context
     */
    private function result(string $status, string $message, bool $isBlocking = false, ?array $context = null): DoctorResult
    {
        return new DoctorResult(
            key: $this->key(),
            package: $this->package(),
            status: $status,
            message: $message,
            isBlocking: $isBlocking,
            context: $context,
        );
    }
}
