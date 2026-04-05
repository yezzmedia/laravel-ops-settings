<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use YezzMedia\OpsSettings\Support\OpsSettingsStoreSetup;

/**
 * In-memory fake for OpsSettingsStoreSetup.
 * Allows install-step tests to exercise flow logic without hitting the filesystem or database.
 */
class FakeOpsSettingsStoreSetup extends OpsSettingsStoreSetup
{
    /** @var array<int, string> */
    public array $calls = [];

    public bool $migrationsWereForced = false;

    public function __construct(
        public bool $hasMigrationsPublished = false,
        public bool $hasReadyStore = false,
        public bool $hasPendingMigrations = false,
        public bool $migrationSucceeds = true,
    ) {}

    public function migrationsPublished(): bool
    {
        return $this->hasMigrationsPublished;
    }

    public function publishMigrations(bool $force = false): void
    {
        $this->calls[] = 'publish_migrations';
        $this->hasMigrationsPublished = true;
        $this->migrationsWereForced = $force;
    }

    public function storeReady(): bool
    {
        return $this->hasReadyStore;
    }

    public function hasPendingMigrations(): bool
    {
        return $this->hasPendingMigrations;
    }

    public function runMigrations(): void
    {
        $this->calls[] = 'run_migrations';

        if ($this->migrationSucceeds) {
            $this->hasReadyStore = true;
            $this->hasPendingMigrations = false;
        }
    }
}
