<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Controls package-owned settings read caching. When enabled, OpsSettingsManager
    | caches grouped reads forever and invalidates them explicitly on mutation.
    |
    */

    'cache' => [
        'enabled' => true,
        'store' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    |
    | Controls optional persistence for normalized ops-settings audit events.
    | The package remains runtime-safe when no audit backend is configured.
    |
    */

    'audit' => [
        'driver' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Defaults Configuration
    |--------------------------------------------------------------------------
    |
    | Controls seeding behavior during explicit install flow only.
    | This does not trigger ordinary runtime boot seeding.
    |
    */

    'defaults' => [
        'seed_on_install' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Workspace Configuration
    |--------------------------------------------------------------------------
    |
    | Controls package-owned UX helpers such as import/export history windows
    | and the curated preset list available to operators.
    |
    */

    'workspace' => [
        'history_limit' => 20,
        'presets' => ['de', 'ch', 'at', 'us'],
    ],

];
