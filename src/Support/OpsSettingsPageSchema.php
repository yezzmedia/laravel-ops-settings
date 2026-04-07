<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Locales;

final class OpsSettingsPageSchema
{
    public static function intro(OpsSettingsGroup $group): string
    {
        return match ($group) {
            OpsSettingsGroup::Identity => 'Identity settings establish the naming baseline for the wider platform. Downstream packages should be able to reuse these values without reinterpreting what the operator is called.',
            OpsSettingsGroup::Contact => 'Contact settings define the reusable support, messaging, and postal information for the platform. Use this tab to keep customer-facing contact details accurate and easy to maintain.',
            OpsSettingsGroup::Brand => 'Brand settings provide the reusable naming, copy, visual defaults, and asset references that help multiple package surfaces feel like one coherent platform.',
            OpsSettingsGroup::Social => 'Social settings centralize the official public profiles for the platform. These values should be stable enough to embed in multiple footers, profile blocks, and operator-maintained pages.',
            OpsSettingsGroup::Legal => 'Legal settings hold the shared legal identity of the platform. Operators should keep these values accurate, auditable, and aligned with the current entity behind the service.',
            OpsSettingsGroup::WebsiteDefaults => 'Website defaults provide reusable locale, formatting, and messaging patterns for downstream packages. Use them when a package needs a sensible fallback instead of inventing its own baseline.',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function highlights(OpsSettingsGroup $group): array
    {
        return match ($group) {
            OpsSettingsGroup::Identity => [
                'operator naming shown in shared admin and ops experiences',
                'platform labels used when the main brand needs clarification',
                'stable values that other teams can reference consistently',
            ],
            OpsSettingsGroup::Contact => [
                'support channels used across public and operator-facing experiences',
                'postal address data for trust, legal, and support surfaces',
                'consistent contact details that downstream teams can reuse without editing',
            ],
            OpsSettingsGroup::Brand => [
                'shared brand naming, claim, and email sender defaults',
                'palette references that support consistent UI styling',
                'stable asset references for downstream design systems and pages',
            ],
            OpsSettingsGroup::Social => [
                'official platform-owned social destinations',
                'consistent external links for websites and support surfaces',
                'one reusable source of truth for public profile URLs',
            ],
            OpsSettingsGroup::Legal => [
                'formal legal identity used across notices and compliance surfaces',
                'registration and policy references needed for trust and billing contexts',
                'privacy contact details and reusable legal notice copy',
            ],
            OpsSettingsGroup::WebsiteDefaults => [
                'fallback localization defaults for downstream websites and package pages',
                'shared footer, support, and CTA wording for multi-surface consistency',
                'reusable formatting rules that keep operator-facing language aligned',
            ],
        };
    }

    public static function example(OpsSettingsGroup $group): string
    {
        return match ($group) {
            OpsSettingsGroup::Identity => 'Example: use `Yezz Media` as the operator name and `Platform Operations` as the platform label when you want one formal owner plus one clarifying operational label.',
            OpsSettingsGroup::Contact => 'Example: point support email to a monitored shared inbox, store one internationally readable phone number, expose one canonical support URL, and keep legal and postal contact details aligned.',
            OpsSettingsGroup::Brand => 'Example: define one polished brand name, one timeless tagline and claim, one consistent sender name for email, and stable asset references so package UIs can stay aligned.',
            OpsSettingsGroup::Social => 'Example: keep only the official branded profiles here, and leave temporary campaign accounts or experimental channels out of the shared defaults.',
            OpsSettingsGroup::Legal => 'Example: use the exact registered entity name, current registration and VAT identifiers, one monitored privacy inbox, and the current public legal policy URLs so compliance surfaces stay consistent and reviewable.',
            OpsSettingsGroup::WebsiteDefaults => 'Example: define `de` and `en` locale fallbacks, a stable timezone, one default currency, and neutral support wording that works across multiple layouts.',
        };
    }

    /**
     * @return array<int, Section>
     */
    public static function schema(OpsSettingsGroup $group): array
    {
        return match ($group) {
            OpsSettingsGroup::Identity => self::identitySchema(),
            OpsSettingsGroup::Contact => self::contactSchema(),
            OpsSettingsGroup::Brand => self::brandSchema(),
            OpsSettingsGroup::Social => self::socialSchema(),
            OpsSettingsGroup::Legal => self::legalSchema(),
            OpsSettingsGroup::WebsiteDefaults => self::websiteDefaultsSchema(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function currentData(OpsSettingsManager $manager, OpsSettingsGroup $group): array
    {
        return match ($group) {
            OpsSettingsGroup::Identity => [
                'name' => $manager->identity()->name,
                'platform_label' => $manager->identity()->platform_label,
            ],
            OpsSettingsGroup::Contact => [
                'support_email' => $manager->contact()->support_email,
                'noreply_email' => $manager->contact()->noreply_email,
                'contact_phone' => $manager->contact()->contact_phone,
                'contact_whatsapp' => $manager->contact()->contact_whatsapp,
                'support_url' => $manager->contact()->support_url,
                'support_chat_url' => $manager->contact()->support_chat_url,
                'support_hours' => $manager->contact()->support_hours,
                'address_line_1' => $manager->contact()->address_line_1,
                'address_line_2' => $manager->contact()->address_line_2,
                'postal_code' => $manager->contact()->postal_code,
                'city' => $manager->contact()->city,
                'country_code' => $manager->contact()->country_code,
            ],
            OpsSettingsGroup::Brand => [
                'brand_name' => $manager->brand()->brand_name,
                'brand_tagline' => $manager->brand()->brand_tagline,
                'brand_claim' => $manager->brand()->brand_claim,
                'default_email_from_name' => $manager->brand()->default_email_from_name,
                'logo_reference' => $manager->brand()->logo_reference,
                'favicon_reference' => $manager->brand()->favicon_reference,
                'icon_reference' => $manager->brand()->icon_reference,
                'email_logo_reference' => $manager->brand()->email_logo_reference,
                'primary_color' => $manager->brand()->primary_color,
                'secondary_color' => $manager->brand()->secondary_color,
            ],
            OpsSettingsGroup::Social => [
                'facebook_url' => $manager->social()->facebook_url,
                'instagram_url' => $manager->social()->instagram_url,
                'linkedin_url' => $manager->social()->linkedin_url,
                'x_url' => $manager->social()->x_url,
                'youtube_url' => $manager->social()->youtube_url,
                'tiktok_url' => $manager->social()->tiktok_url,
                'threads_url' => $manager->social()->threads_url,
                'github_url' => $manager->social()->github_url,
                'mastodon_url' => $manager->social()->mastodon_url,
                'telegram_url' => $manager->social()->telegram_url,
            ],
            OpsSettingsGroup::Legal => [
                'legal_entity_name' => $manager->legal()->legal_entity_name,
                'managing_director' => $manager->legal()->managing_director,
                'registration_number' => $manager->legal()->registration_number,
                'registration_court' => $manager->legal()->registration_court,
                'vat_id' => $manager->legal()->vat_id,
                'privacy_contact_email' => $manager->legal()->privacy_contact_email,
                'imprint_url' => $manager->legal()->imprint_url,
                'privacy_policy_url' => $manager->legal()->privacy_policy_url,
                'terms_url' => $manager->legal()->terms_url,
                'cookie_policy_url' => $manager->legal()->cookie_policy_url,
                'legal_notice_snippet' => $manager->legal()->legal_notice_snippet,
            ],
            OpsSettingsGroup::WebsiteDefaults => [
                'default_site_title_pattern' => $manager->websiteDefaults()->default_site_title_pattern,
                'default_footer_label' => $manager->websiteDefaults()->default_footer_label,
                'default_support_label' => $manager->websiteDefaults()->default_support_label,
                'default_support_cta_label' => $manager->websiteDefaults()->default_support_cta_label,
                'default_reply_to_email' => $manager->websiteDefaults()->default_reply_to_email,
                'default_locale' => $manager->websiteDefaults()->default_locale,
                'fallback_locale' => $manager->websiteDefaults()->fallback_locale,
                'default_timezone' => $manager->websiteDefaults()->default_timezone,
                'default_currency' => $manager->websiteDefaults()->default_currency,
                'default_date_format' => $manager->websiteDefaults()->default_date_format,
                'default_time_format' => $manager->websiteDefaults()->default_time_format,
            ],
        };
    }

    /**
     * @return array<int, Section>
     */
    private static function identitySchema(): array
    {
        return [
            Section::make('Operator Identity')
                ->description('Define the core operator name and optional platform label that other packages should treat as the canonical naming source.')
                ->schema([
                    TextInput::make('name')
                        ->label('Operator Name')
                        ->placeholder('Yezz Media')
                        ->helperText('Use the stable public-facing operator or company name that should appear across shared platform surfaces.')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('platform_label')
                        ->label('Platform Label')
                        ->placeholder('Operations Cloud')
                        ->helperText('Optional sub-brand or environment label for cases where the operator name needs a second clarifying layer.')
                        ->maxLength(255),
                ]),
        ];
    }

    /**
     * @return array<int, Section>
     */
    private static function contactSchema(): array
    {
        return [
            Section::make('Support Channels')
                ->description('Keep the primary support and outbound communication contacts here so packages do not invent their own variations.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('support_email')
                                ->label('Support Email')
                                ->placeholder('support@example.com')
                                ->helperText('Use the main monitored support inbox that public pages and operator workflows should reuse.')
                                ->email()
                                ->hint('Public-facing primary support address')
                                ->maxLength(255),
                            TextInput::make('noreply_email')
                                ->label('No-Reply Email')
                                ->placeholder('noreply@example.com')
                                ->helperText('Optional outbound-only sender address for platform emails that should not receive direct replies.')
                                ->email()
                                ->hint('Keep distinct from the support inbox')
                                ->maxLength(255),
                            TextInput::make('contact_phone')
                                ->label('Contact Phone')
                                ->placeholder('+49 30 1234 5678')
                                ->helperText('Prefer one internationally readable contact number with a stable country code format.')
                                ->tel()
                                ->maxLength(50),
                            TextInput::make('contact_whatsapp')
                                ->label('WhatsApp Contact')
                                ->placeholder('+49 30 1234 5678')
                                ->helperText('Optional WhatsApp-compatible support number when that channel is officially monitored.')
                                ->tel()
                                ->maxLength(50),
                        ]),
                ]),
            Section::make('Support Experience')
                ->description('Use canonical support destinations and service-hour guidance that multiple packages can present consistently.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('support_url')
                                ->label('Support URL')
                                ->placeholder('https://example.com/support')
                                ->helperText('Canonical public support or help-center URL.')
                                ->url()
                                ->suffixAction(self::visitUrlAction('support_url'))
                                ->maxLength(500),
                            TextInput::make('support_chat_url')
                                ->label('Support Chat URL')
                                ->placeholder('https://example.com/live-chat')
                                ->helperText('Optional public chat entry point for support or concierge workflows.')
                                ->url()
                                ->suffixAction(self::visitUrlAction('support_chat_url'))
                                ->maxLength(500),
                        ]),
                    Textarea::make('support_hours')
                        ->label('Support Hours')
                        ->placeholder('Mon-Fri 09:00-17:00 CET')
                        ->helperText('Short operator-approved service-hours text that websites and support widgets can reuse.')
                        ->rows(4),
                ]),
            Section::make('Address')
                ->description('Use the postal details that should appear on legal, support, and trust-building surfaces.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('address_line_1')
                                ->label('Address Line 1')
                                ->placeholder('Example Street 12')
                                ->helperText('Street and house number or the clearest first postal line.')
                                ->maxLength(255),
                            TextInput::make('address_line_2')
                                ->label('Address Line 2')
                                ->placeholder('2nd floor, Office 4')
                                ->helperText('Optional additional routing details such as building, floor, or mailbox.')
                                ->maxLength(255),
                            TextInput::make('postal_code')
                                ->label('Postal Code')
                                ->placeholder('10115')
                                ->helperText('Store the postal code in the standard format used in the destination country.')
                                ->maxLength(20),
                            TextInput::make('city')
                                ->label('City')
                                ->placeholder('Berlin')
                                ->helperText('Use the full city name as it should appear in customer-facing contexts.')
                                ->maxLength(100),
                            Select::make('country_code')
                                ->label('Country Code')
                                ->placeholder('Select a country code')
                                ->helperText('Use the ISO 3166-1 alpha-2 code, for example `DE`, `FR`, or `US`.')
                                ->searchable()
                                ->options(self::countryOptions()),
                        ]),
                ]),
        ];
    }

    /**
     * @return array<int, Section>
     */
    private static function brandSchema(): array
    {
        return [
            Section::make('Brand Identity')
                ->description('Capture the copy that should define the shared platform brand wherever no package-specific override exists.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('brand_name')
                                ->label('Brand Name')
                                ->placeholder('Yezz Platform')
                                ->helperText('Use the short, presentation-ready brand name that should appear in headers, titles, and product copy.')
                                ->maxLength(255),
                            TextInput::make('default_email_from_name')
                                ->label('Default Email From Name')
                                ->placeholder('Yezz Platform')
                                ->helperText('Reusable sender display name for transactional or operator-facing email defaults.')
                                ->hint('Used when no package-specific sender name exists')
                                ->maxLength(255),
                            TextInput::make('brand_tagline')
                                ->label('Brand Tagline')
                                ->placeholder('Operations made visible')
                                ->helperText('Optional concise statement that communicates tone and positioning without becoming campaign-specific.')
                                ->maxLength(500),
                            TextInput::make('brand_claim')
                                ->label('Brand Claim')
                                ->placeholder('Built for operator clarity')
                                ->helperText('Optional secondary claim or promise that helps downstream pages describe the platform consistently.')
                                ->maxLength(500),
                        ]),
                ]),
            Section::make('Asset References')
                ->description('Store stable internal references for shared brand assets rather than package-local filenames or arbitrary remote URLs.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('logo_reference')
                                ->label('Logo Reference')
                                ->placeholder('brand/primary-lockup')
                                ->helperText('Stable internal asset reference for the primary logo.')
                                ->hint('Internal-only asset key')
                                ->maxLength(500),
                            TextInput::make('favicon_reference')
                                ->label('Favicon Reference')
                                ->placeholder('brand/favicon')
                                ->helperText('Internal asset reference for browser favicon usage.')
                                ->maxLength(500),
                            TextInput::make('icon_reference')
                                ->label('Icon Reference')
                                ->placeholder('brand/icon-mark')
                                ->helperText('Internal asset reference for compact icon-only usage.')
                                ->maxLength(500),
                            TextInput::make('email_logo_reference')
                                ->label('Email Logo Reference')
                                ->placeholder('brand/email-header')
                                ->helperText('Optional internal asset reference for email-specific logo usage.')
                                ->maxLength(500),
                        ]),
                ]),
            Section::make('Brand Colors')
                ->description('Use colors that can safely become reusable defaults across package-owned surfaces.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('primary_color')
                                ->label('Primary Color')
                                ->placeholder('#1F3A5F')
                                ->helperText('Use a hex value that represents the main platform color and is safe for repeated reuse.')
                                ->hint('Example preview: '.self::colorPreview('#1F3A5F'))
                                ->maxLength(20),
                            TextInput::make('secondary_color')
                                ->label('Secondary Color')
                                ->placeholder('#9F7AEA')
                                ->helperText('Use a supporting hex value that complements the primary brand color.')
                                ->hint('Example preview: '.self::colorPreview('#9F7AEA'))
                                ->maxLength(20),
                        ]),
                ]),
        ];
    }

    /**
     * @return array<int, Section>
     */
    private static function socialSchema(): array
    {
        return [
            Section::make('Core Social Channels')
                ->description('Keep only official public profiles here so downstream pages can expose them confidently.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            self::urlInput('facebook_url', 'Facebook URL', 'https://www.facebook.com/your-brand', 'Use the full canonical profile URL, including protocol, rather than a partial handle.'),
                            self::urlInput('instagram_url', 'Instagram URL', 'https://www.instagram.com/your-brand', 'Store the public profile URL that should be linked from websites or profile sections.'),
                            self::urlInput('linkedin_url', 'LinkedIn URL', 'https://www.linkedin.com/company/your-brand', 'Prefer the company or organization page URL, not a personal profile, unless that is intentional.'),
                            self::urlInput('x_url', 'X (Twitter) URL', 'https://x.com/your-brand', 'Use the canonical public profile URL so links remain stable if the platform is reused elsewhere.'),
                            self::urlInput('youtube_url', 'YouTube URL', 'https://www.youtube.com/@your-brand', 'Store the channel or handle URL that should represent the official video presence.'),
                        ]),
                ]),
            Section::make('Additional Public Channels')
                ->description('Add only channels that are official, stable, and safe for reuse across the broader platform.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            self::urlInput('tiktok_url', 'TikTok URL', 'https://www.tiktok.com/@your-brand', 'Official TikTok profile URL for public linking.'),
                            self::urlInput('threads_url', 'Threads URL', 'https://www.threads.net/@your-brand', 'Official Threads profile URL for public linking.'),
                            self::urlInput('github_url', 'GitHub URL', 'https://github.com/your-brand', 'Official GitHub organization or profile URL.'),
                            self::urlInput('mastodon_url', 'Mastodon URL', 'https://mastodon.social/@your-brand', 'Canonical Mastodon profile URL on the correct instance.'),
                            self::urlInput('telegram_url', 'Telegram URL', 'https://t.me/your-brand', 'Official Telegram channel or public profile URL.'),
                        ]),
                ]),
        ];
    }

    /**
     * @return array<int, Section>
     */
    private static function legalSchema(): array
    {
        return [
            Section::make('Legal Entity')
                ->description('Use the formal legal data that public-facing and compliance-related platform surfaces can rely on.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('legal_entity_name')
                                ->label('Legal Entity Name')
                                ->placeholder('Yezz Media GmbH')
                                ->helperText('Store the formal registered legal entity name exactly as it should appear in legal and compliance contexts.')
                                ->maxLength(255),
                            TextInput::make('managing_director')
                                ->label('Managing Director')
                                ->placeholder('Jane Example')
                                ->helperText('Optional responsible managing director or equivalent executive name for legal disclosures.')
                                ->maxLength(255),
                            TextInput::make('registration_number')
                                ->label('Registration Number')
                                ->placeholder('HRB 123456')
                                ->helperText('Use the official commercial or registry number in the format expected by your jurisdiction.')
                                ->maxLength(100),
                            TextInput::make('registration_court')
                                ->label('Registration Court')
                                ->placeholder('Berlin-Charlottenburg')
                                ->helperText('Optional court or registry authority connected to the registration number.')
                                ->maxLength(255),
                            TextInput::make('vat_id')
                                ->label('VAT ID')
                                ->placeholder('DE123456789')
                                ->helperText('Store the tax or VAT identifier in the exact format used for invoices and legal notices.')
                                ->maxLength(50),
                            TextInput::make('privacy_contact_email')
                                ->label('Privacy Contact Email')
                                ->placeholder('privacy@example.com')
                                ->helperText('Use a monitored contact point for privacy and regulatory requests.')
                                ->email()
                                ->maxLength(255),
                        ]),
                ]),
            Section::make('Public Legal Pages')
                ->description('Store the canonical public destinations for legal and policy pages that downstream websites can link directly.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            self::urlInput('imprint_url', 'Imprint URL', 'https://example.com/imprint', 'Canonical public imprint or legal notice page URL.'),
                            self::urlInput('privacy_policy_url', 'Privacy Policy URL', 'https://example.com/privacy', 'Canonical privacy policy page URL.'),
                            self::urlInput('terms_url', 'Terms URL', 'https://example.com/terms', 'Canonical terms and conditions page URL.'),
                            self::urlInput('cookie_policy_url', 'Cookie Policy URL', 'https://example.com/cookies', 'Canonical cookie policy or cookie notice page URL.'),
                        ]),
                ]),
            Section::make('Legal Notice')
                ->description('Use concise legal notice copy that can be reused safely before package-specific expansion is needed.')
                ->schema([
                    Textarea::make('legal_notice_snippet')
                        ->label('Legal Notice Snippet')
                        ->placeholder('Managing directors, registered office, and other short mandatory disclosure notes.')
                        ->helperText('Prefer concise, reusable notice text that remains valid across multiple pages and package integrations.')
                        ->rows(5),
                ]),
        ];
    }

    /**
     * @return array<int, Section>
     */
    private static function websiteDefaultsSchema(): array
    {
        return [
            Section::make('Localization Defaults')
                ->description('Define reusable locale and regional defaults that multiple websites or package-owned pages can share.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('default_locale')
                                ->label('Default Locale')
                                ->placeholder('Select a default locale')
                                ->helperText('Primary application or website locale code used as a fallback.')
                                ->searchable()
                                ->options(self::localeOptions()),
                            Select::make('fallback_locale')
                                ->label('Fallback Locale')
                                ->placeholder('Select a fallback locale')
                                ->helperText('Secondary locale code used when translated content is missing.')
                                ->searchable()
                                ->options(self::localeOptions()),
                            Select::make('default_timezone')
                                ->label('Default Timezone')
                                ->placeholder('Select a default timezone')
                                ->helperText('Canonical timezone identifier used across scheduling and display fallbacks.')
                                ->searchable()
                                ->options(self::timezoneOptions()),
                            Select::make('default_currency')
                                ->label('Default Currency')
                                ->placeholder('Select a default currency')
                                ->helperText('ISO 4217 currency code used for price or billing fallbacks.')
                                ->searchable()
                                ->options(self::currencyOptions()),
                        ]),
                ]),
            Section::make('Formatting Defaults')
                ->description('Store stable formatting hints that package-owned surfaces can reuse consistently.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('default_date_format')
                                ->label('Default Date Format')
                                ->placeholder('Select a date format')
                                ->helperText('Choose one of the most common date format templates. The preview shows how the format actually looks.')
                                ->searchable()
                                ->options(self::dateFormatOptions()),
                            Select::make('default_time_format')
                                ->label('Default Time Format')
                                ->placeholder('Select a time format')
                                ->helperText('Choose one of the most common time format templates. The preview shows how the format actually looks.')
                                ->searchable()
                                ->options(self::timeFormatOptions()),
                        ]),
                ]),
            Section::make('Messaging Defaults')
                ->description('Define reusable fallback copy patterns that multiple websites or package-owned pages can share.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('default_site_title_pattern')
                                ->label('Default Site Title Pattern')
                                ->placeholder('%s | Yezz Platform')
                                ->helperText('Use a reusable title pattern where downstream pages can inject their own page title into a stable suffix or prefix.')
                                ->maxLength(255),
                            TextInput::make('default_footer_label')
                                ->label('Default Footer Label')
                                ->placeholder('Operated by Yezz Media')
                                ->helperText('Use a short footer phrase that can appear across package-owned websites without needing local overrides.')
                                ->maxLength(255),
                            TextInput::make('default_support_label')
                                ->label('Default Support Label')
                                ->placeholder('Need help? Contact our support team.')
                                ->helperText('Use a neutral support label that can fit help sections, contact cards, or fallback support blocks.')
                                ->maxLength(255),
                            TextInput::make('default_support_cta_label')
                                ->label('Default Support CTA Label')
                                ->placeholder('Contact support')
                                ->helperText('Short reusable call-to-action label for support buttons or links.')
                                ->maxLength(255),
                            TextInput::make('default_reply_to_email')
                                ->label('Default Reply-To Email')
                                ->placeholder('support@example.com')
                                ->helperText('Optional reply-to email fallback for downstream mail integrations.')
                                ->email()
                                ->maxLength(255),
                        ]),
                ]),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function localeOptions(): array
    {
        if (class_exists(Locales::class)) {
            $options = Locales::getNames('en');
            ksort($options, SORT_NATURAL | SORT_FLAG_CASE);

            return $options;
        }

        return [
            'de' => 'German',
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'it' => 'Italian',
            'nl' => 'Dutch',
            'pt' => 'Portuguese',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function countryOptions(): array
    {
        if (class_exists(Countries::class)) {
            $options = [];

            foreach (Countries::getNames('en') as $countryCode => $countryName) {
                $options[$countryCode] = sprintf('%s (%s)', $countryName, $countryCode);
            }

            asort($options, SORT_NATURAL | SORT_FLAG_CASE);

            return $options;
        }

        return [
            'AT' => 'Austria (AT)',
            'CH' => 'Switzerland (CH)',
            'DE' => 'Germany (DE)',
            'US' => 'United States (US)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function timezoneOptions(): array
    {
        $options = [];

        foreach (\DateTimeZone::listIdentifiers() as $timezone) {
            $options[$timezone] = str_replace('_', ' ', $timezone);
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function currencyOptions(): array
    {
        if (class_exists(Currencies::class)) {
            $options = [];

            foreach (Currencies::getNames('en') as $currency => $name) {
                $options[$currency] = sprintf('%s (%s)', $name, $currency);
            }

            asort($options, SORT_NATURAL | SORT_FLAG_CASE);

            return $options;
        }

        return [
            'CHF' => 'Swiss Franc (CHF)',
            'EUR' => 'Euro (EUR)',
            'GBP' => 'British Pound (GBP)',
            'USD' => 'US Dollar (USD)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function dateFormatOptions(): array
    {
        return [
            'd.m.Y' => self::formatPreview('d.m.Y'),
            'd/m/Y' => self::formatPreview('d/m/Y'),
            'Y-m-d' => self::formatPreview('Y-m-d'),
            'j. M Y' => self::formatPreview('j. M Y'),
            'M j, Y' => self::formatPreview('M j, Y'),
            'l, d. F Y' => self::formatPreview('l, d. F Y'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function timeFormatOptions(): array
    {
        return [
            'H:i' => self::formatPreview('H:i'),
            'H:i:s' => self::formatPreview('H:i:s'),
            'h:i A' => self::formatPreview('h:i A'),
            'g:i A' => self::formatPreview('g:i A'),
            'h:i:s A' => self::formatPreview('h:i:s A'),
        ];
    }

    private static function formatPreview(string $format): string
    {
        $preview = (new \DateTimeImmutable('2026-12-31 17:45:08', new \DateTimeZone('UTC')))->format($format);

        return sprintf('%s (%s)', $preview, $format);
    }

    private static function colorPreview(string $color): string
    {
        return sprintf('%s sample', $color);
    }

    private static function visitUrlAction(string $field): Action
    {
        return Action::make('visit_'.$field)
            ->label('Open')
            ->icon('heroicon-m-arrow-top-right-on-square')
            ->url(fn ($get): ?string => filled($get($field)) ? (string) $get($field) : null)
            ->openUrlInNewTab();
    }

    private static function urlInput(string $name, string $label, string $placeholder, string $helperText): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->placeholder($placeholder)
            ->helperText($helperText)
            ->url()
            ->maxLength(500);
    }
}
