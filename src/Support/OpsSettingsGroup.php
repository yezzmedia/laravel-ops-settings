<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use InvalidArgumentException;
use YezzMedia\OpsSettings\Settings\OperatorIdentitySettings;
use YezzMedia\OpsSettings\Settings\PlatformBrandSettings;
use YezzMedia\OpsSettings\Settings\PlatformContactSettings;
use YezzMedia\OpsSettings\Settings\PlatformLegalSettings;
use YezzMedia\OpsSettings\Settings\PlatformSocialSettings;
use YezzMedia\OpsSettings\Settings\PlatformWebsiteDefaultsSettings;

/**
 * Approved group identifier surface for mutation and event payloads in V1.
 * Free string group identifiers are not approved for the stable V1 mutation surface.
 */
enum OpsSettingsGroup: string
{
    case Identity = 'identity';
    case Contact = 'contact';
    case Brand = 'brand';
    case Social = 'social';
    case Legal = 'legal';
    case WebsiteDefaults = 'website_defaults';

    /**
     * Returns the fully-qualified settings class for this group.
     *
     * @return class-string
     */
    public function settingsClass(): string
    {
        return match ($this) {
            self::Identity => OperatorIdentitySettings::class,
            self::Contact => PlatformContactSettings::class,
            self::Brand => PlatformBrandSettings::class,
            self::Social => PlatformSocialSettings::class,
            self::Legal => PlatformLegalSettings::class,
            self::WebsiteDefaults => PlatformWebsiteDefaultsSettings::class,
        };
    }

    /**
     * Returns the package-namespaced cache key for this group.
     * Pattern: ops_settings.{group_value}
     */
    public function cacheKey(): string
    {
        return 'ops_settings.'.$this->value;
    }

    public function label(): string
    {
        return match ($this) {
            self::Identity => 'Identity',
            self::Contact => 'Contact',
            self::Brand => 'Brand',
            self::Social => 'Social',
            self::Legal => 'Legal',
            self::WebsiteDefaults => 'Website Defaults',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Identity => 'heroicon-o-identification',
            self::Contact => 'heroicon-o-phone',
            self::Brand => 'heroicon-o-paint-brush',
            self::Social => 'heroicon-o-share',
            self::Legal => 'heroicon-o-document-text',
            self::WebsiteDefaults => 'heroicon-o-globe-alt',
        };
    }

    /**
     * Returns the approved property names for this group.
     * Unknown attributes must fail fast.
     *
     * @return array<int, string>
     */
    public function approvedProperties(): array
    {
        $class = $this->settingsClass();
        $reflection = new \ReflectionClass($class);

        return array_values(array_map(
            fn (\ReflectionProperty $prop) => $prop->getName(),
            array_filter(
                $reflection->getProperties(\ReflectionProperty::IS_PUBLIC),
                fn (\ReflectionProperty $prop) => $prop->getDeclaringClass()->getName() === $class,
            ),
        ));
    }

    /**
     * @return array<int, string>
     */
    public function requiredProperties(): array
    {
        return match ($this) {
            self::Identity => ['name'],
            self::Contact => ['support_email'],
            self::Brand => [],
            self::Social => [],
            self::Legal => ['legal_entity_name', 'privacy_contact_email'],
            self::WebsiteDefaults => ['default_locale', 'fallback_locale', 'default_timezone'],
        };
    }

    /**
     * @return array<int, string>
     */
    public function publicProperties(): array
    {
        return match ($this) {
            self::Brand => array_values(array_diff($this->approvedProperties(), [
                'logo_reference',
                'favicon_reference',
                'icon_reference',
                'email_logo_reference',
            ])),
            default => $this->approvedProperties(),
        };
    }

    /**
     * Resolves a group from a string value, failing fast on unknown groups.
     *
     * @throws InvalidArgumentException
     */
    public static function fromValue(string $value): self
    {
        $group = self::tryFrom($value);

        if ($group === null) {
            throw new InvalidArgumentException(sprintf(
                'Unknown settings group [%s]. Supported groups: %s.',
                $value,
                implode(', ', array_column(self::cases(), 'value')),
            ));
        }

        return $group;
    }
}
