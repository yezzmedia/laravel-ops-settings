<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use YezzMedia\OpsSettings\OpsSettingsServiceProvider;

/**
 * Owns host-side setup checks and actions for the ops settings store.
 * Used exclusively in explicit install context, not in ordinary runtime boot.
 */
class OpsSettingsStoreSetup
{
    /**
     * Checks whether the base settings table (from spatie/laravel-settings) exists.
     */
    public function settingsTableExists(): bool
    {
        return Schema::hasTable('settings');
    }

    /**
     * Checks whether the ops-settings package migrations have been published
     * to the host application's database/migrations directory.
     */
    public function migrationsPublished(): bool
    {
        $publishedPath = database_path('migrations');

        foreach ($this->publishableMigrationNames() as $name) {
            $matches = glob($publishedPath.'/*_'.$name.'.php');

            if (empty($matches)) {
                return false;
            }
        }

        return ! empty($this->publishableMigrationNames());
    }

    /**
     * Checks whether any published ops-settings migration has not yet been applied.
     */
    public function hasPendingMigrations(): bool
    {
        if (! Schema::hasTable('migrations')) {
            return $this->migrationsPublished();
        }

        $applied = $this->appliedMigrationNames();
        $publishedPath = database_path('migrations');

        foreach ($this->publishableMigrationNames() as $name) {
            $matches = glob($publishedPath.'/*_'.$name.'.php');

            if (empty($matches)) {
                continue;
            }

            $migrationKey = basename($matches[0], '.php');

            if (! in_array($migrationKey, $applied, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the settings store is fully ready for ops-settings runtime use.
     */
    public function storeReady(): bool
    {
        return $this->settingsTableExists()
            && ! $this->hasPendingMigrations();
    }

    /**
     * Publishes the package-owned settings migrations to the host application.
     */
    public function publishMigrations(bool $force = false): void
    {
        $options = [
            '--provider' => OpsSettingsServiceProvider::class,
            '--tag' => ['laravel-ops-settings-migrations'],
        ];

        if ($force) {
            $options['--force'] = true;
        }

        Artisan::call('vendor:publish', $options);
    }

    /**
     * Runs pending settings migrations using standard Laravel migrate.
     */
    public function runMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    /**
     * Returns the bare names of all publishable ops-settings migrations.
     *
     * @return array<int, string>
     */
    private function publishableMigrationNames(): array
    {
        return [
            'add_operator_identity_settings',
            'add_platform_contact_settings',
            'add_platform_brand_settings',
            'add_platform_social_settings',
            'add_platform_legal_settings',
            'add_platform_website_defaults_settings',
        ];
    }

    /**
     * Returns the names of all already-applied migrations from the migrations table.
     *
     * @return array<int, string>
     */
    private function appliedMigrationNames(): array
    {
        if (! Schema::hasTable('migrations')) {
            return [];
        }

        return DB::table('migrations')
            ->pluck('migration')
            ->values()
            ->all();
    }
}
