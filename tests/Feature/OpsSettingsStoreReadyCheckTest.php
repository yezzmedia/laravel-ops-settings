<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use YezzMedia\OpsSettings\Doctor\OpsSettingsStoreReadyCheck;
use YezzMedia\OpsSettings\Support\OpsSettingsStoreSetup;

it('passes when the ops settings store is ready', function (): void {
    $result = app(OpsSettingsStoreReadyCheck::class)->run();

    expect($result->status)->toBe('passed')
        ->and($result->isBlocking)->toBeFalse();
});

it('fails when the settings table is missing', function (): void {
    Schema::dropIfExists('settings');

    $result = app(OpsSettingsStoreReadyCheck::class)->run();

    expect($result->status)->toBe('failed')
        ->and($result->isBlocking)->toBeTrue()
        ->and($result->message)->toBe('The ops settings store is not ready because the settings table is missing.');
});

it('reuses the migrations table state across store readiness checks', function (): void {
    $setup = app(OpsSettingsStoreSetup::class);

    DB::flushQueryLog();
    DB::enableQueryLog();

    $setup->storeReady();
    $setup->hasPendingMigrations();

    $queries = collect(DB::getQueryLog())->pluck('query');

    expect($queries->filter(fn (string $query): bool => str_contains($query, "name = 'migrations'") && str_contains($query, 'sqlite_master'))->count())->toBeLessThanOrEqual(1);
});
