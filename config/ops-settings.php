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

];
