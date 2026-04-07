<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

final class OpsSettingsRegionPreset
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function values(string $key): array
    {
        return match ($key) {
            'de' => self::germany(),
            'ch' => self::switzerland(),
            'at' => self::austria(),
            'us' => self::unitedStates(),
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'de' => 'Germany preset',
            'ch' => 'Switzerland preset',
            'at' => 'Austria preset',
            'us' => 'United States preset',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function germany(): array
    {
        return [
            'contact' => ['country_code' => 'DE'],
            'website_defaults' => [
                'default_locale' => 'de',
                'fallback_locale' => 'en',
                'default_timezone' => 'Europe/Berlin',
                'default_currency' => 'EUR',
                'default_date_format' => 'd.m.Y',
                'default_time_format' => 'H:i',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function switzerland(): array
    {
        return [
            'contact' => ['country_code' => 'CH'],
            'website_defaults' => [
                'default_locale' => 'de',
                'fallback_locale' => 'fr',
                'default_timezone' => 'Europe/Zurich',
                'default_currency' => 'CHF',
                'default_date_format' => 'd.m.Y',
                'default_time_format' => 'H:i',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function austria(): array
    {
        return [
            'contact' => ['country_code' => 'AT'],
            'website_defaults' => [
                'default_locale' => 'de',
                'fallback_locale' => 'en',
                'default_timezone' => 'Europe/Vienna',
                'default_currency' => 'EUR',
                'default_date_format' => 'd.m.Y',
                'default_time_format' => 'H:i',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function unitedStates(): array
    {
        return [
            'contact' => ['country_code' => 'US'],
            'website_defaults' => [
                'default_locale' => 'en',
                'fallback_locale' => 'es',
                'default_timezone' => 'America/New_York',
                'default_currency' => 'USD',
                'default_date_format' => 'M j, Y',
                'default_time_format' => 'h:i A',
            ],
        ];
    }
}
