<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\LaravelSettings\LaravelSettingsServiceProvider;
use YezzMedia\Foundation\Testing\FoundationTestCase;
use YezzMedia\OpsSettings\OpsSettingsServiceProvider;

/**
 * Provides a realistic Testbench baseline for ops-settings package tests.
 *
 * Sets up the in-memory SQLite database with the required settings table
 * so that settings classes and install steps can be exercised without a
 * real database. Standard Laravel migrations tracking uses the migrations
 * table, which Testbench creates automatically.
 */
abstract class OpsSettingsTestCase extends FoundationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->ensureSettingsTableExists();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            LaravelSettingsServiceProvider::class,
            OpsSettingsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('settings.driver', 'database');
        $app['config']->set('settings.drivers.database', [
            'driver' => 'database',
            'table' => 'settings',
            'connection' => null,
        ]);

        $app['config']->set('ops-settings.cache.enabled', false);
        $app['config']->set('ops-settings.cache.store', null);
        $app['config']->set('ops-settings.defaults.seed_on_install', true);
    }

    private function ensureSettingsTableExists(): void
    {
        if (Schema::hasTable('settings')) {
            return;
        }

        Schema::create('settings', static function (Blueprint $table): void {
            $table->id();
            $table->string('group')->index();
            $table->string('name');
            $table->boolean('locked')->default(false);
            $table->json('payload');
            $table->timestamps();
            $table->unique(['group', 'name']);
        });
    }
}
