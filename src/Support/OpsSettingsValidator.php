<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Locales;

final class OpsSettingsValidator
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function validate(OpsSettingsGroup $group, array $attributes): array
    {
        $normalized = $this->normalize($attributes);

        $validator = Validator::make($normalized, $this->rules($group), messages: $this->messages());

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first());
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function normalize(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            $attributes[$key] = trim($value);

            if ($attributes[$key] === '') {
                $attributes[$key] = null;
            }
        }

        if (is_string(Arr::get($attributes, 'country_code'))) {
            $attributes['country_code'] = strtoupper((string) $attributes['country_code']);
        }

        foreach (['default_currency'] as $uppercaseKey) {
            if (is_string(Arr::get($attributes, $uppercaseKey))) {
                $attributes[$uppercaseKey] = strtoupper((string) $attributes[$uppercaseKey]);
            }
        }

        return $attributes;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function rules(OpsSettingsGroup $group): array
    {
        $commonString = ['nullable', 'string'];
        $url = ['nullable', 'url:http,https'];

        return match ($group) {
            OpsSettingsGroup::Identity => [
                'name' => ['required', 'string', 'max:255'],
                'platform_label' => ['nullable', 'string', 'max:255'],
            ],
            OpsSettingsGroup::Contact => [
                'support_email' => ['nullable', 'email', 'max:255'],
                'noreply_email' => ['nullable', 'email', 'max:255', 'different:support_email'],
                'contact_phone' => ['nullable', 'string', 'max:50'],
                'contact_whatsapp' => ['nullable', 'string', 'max:50'],
                'support_url' => $url,
                'support_chat_url' => $url,
                'support_hours' => ['nullable', 'string', 'max:1000'],
                'address_line_1' => ['nullable', 'string', 'max:255'],
                'address_line_2' => ['nullable', 'string', 'max:255'],
                'postal_code' => ['nullable', 'string', 'max:20'],
                'city' => ['nullable', 'string', 'max:100'],
                'country_code' => ['nullable', 'string', 'size:2', Rule::in(array_keys(OpsSettingsPageSchema::countryOptions()))],
            ],
            OpsSettingsGroup::Brand => [
                'brand_name' => ['nullable', 'string', 'max:255'],
                'brand_tagline' => ['nullable', 'string', 'max:500'],
                'brand_claim' => ['nullable', 'string', 'max:500'],
                'default_email_from_name' => ['nullable', 'string', 'max:255'],
                'primary_color' => ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
                'secondary_color' => ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
                'logo_reference' => ['nullable', 'string', 'max:500'],
                'favicon_reference' => ['nullable', 'string', 'max:500'],
                'icon_reference' => ['nullable', 'string', 'max:500'],
                'email_logo_reference' => ['nullable', 'string', 'max:500'],
            ],
            OpsSettingsGroup::Social => [
                'facebook_url' => $url,
                'instagram_url' => $url,
                'linkedin_url' => $url,
                'x_url' => $url,
                'youtube_url' => $url,
                'tiktok_url' => $url,
                'threads_url' => $url,
                'github_url' => $url,
                'mastodon_url' => $url,
                'telegram_url' => $url,
            ],
            OpsSettingsGroup::Legal => [
                'legal_entity_name' => ['nullable', 'string', 'max:255'],
                'managing_director' => ['nullable', 'string', 'max:255'],
                'registration_number' => ['nullable', 'string', 'max:100'],
                'registration_court' => ['nullable', 'string', 'max:255'],
                'vat_id' => ['nullable', 'string', 'max:50'],
                'privacy_contact_email' => ['nullable', 'email', 'max:255'],
                'imprint_url' => $url,
                'privacy_policy_url' => $url,
                'terms_url' => $url,
                'cookie_policy_url' => $url,
                'legal_notice_snippet' => ['nullable', 'string', 'max:5000'],
            ],
            OpsSettingsGroup::WebsiteDefaults => [
                'default_site_title_pattern' => ['nullable', 'string', 'max:255'],
                'default_footer_label' => ['nullable', 'string', 'max:255'],
                'default_support_label' => ['nullable', 'string', 'max:255'],
                'default_support_cta_label' => ['nullable', 'string', 'max:255'],
                'default_reply_to_email' => ['nullable', 'email', 'max:255'],
                'default_locale' => ['nullable', Rule::in(array_keys($this->localeOptions()))],
                'fallback_locale' => ['nullable', Rule::in(array_keys($this->localeOptions()))],
                'default_timezone' => ['nullable', 'timezone:all'],
                'default_currency' => ['nullable', Rule::in(array_keys($this->currencyOptions()))],
                'default_date_format' => ['nullable', Rule::in(array_keys(OpsSettingsPageSchema::dateFormatOptions()))],
                'default_time_format' => ['nullable', Rule::in(array_keys(OpsSettingsPageSchema::timeFormatOptions()))],
            ],
        };
    }

    /**
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'country_code.in' => 'The country code must be a supported ISO country code.',
            'default_timezone.timezone' => 'The default timezone must be a valid timezone identifier.',
            'primary_color.regex' => 'The primary color must be a valid hex color like #1F3A5F.',
            'secondary_color.regex' => 'The secondary color must be a valid hex color like #9F7AEA.',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function localeOptions(): array
    {
        if (class_exists(Locales::class)) {
            return Locales::getNames('en');
        }

        return ['de' => 'German', 'en' => 'English'];
    }

    /**
     * @return array<string, string>
     */
    private function currencyOptions(): array
    {
        if (class_exists(Currencies::class)) {
            $currencies = [];

            foreach (Currencies::getNames('en') as $code => $name) {
                $currencies[$code] = $name;
            }

            return $currencies;
        }

        return ['CHF' => 'Swiss Franc', 'EUR' => 'Euro', 'USD' => 'US Dollar'];
    }
}
