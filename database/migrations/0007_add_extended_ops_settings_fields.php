<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contact.noreply_email', null);
        $this->migrator->add('contact.contact_whatsapp', null);
        $this->migrator->add('contact.support_url', null);
        $this->migrator->add('contact.support_chat_url', null);
        $this->migrator->add('contact.support_hours', null);

        $this->migrator->add('brand.brand_claim', null);
        $this->migrator->add('brand.default_email_from_name', null);
        $this->migrator->add('brand.favicon_reference', null);
        $this->migrator->add('brand.icon_reference', null);
        $this->migrator->add('brand.email_logo_reference', null);

        $this->migrator->add('social.tiktok_url', null);
        $this->migrator->add('social.threads_url', null);
        $this->migrator->add('social.github_url', null);
        $this->migrator->add('social.mastodon_url', null);
        $this->migrator->add('social.telegram_url', null);

        $this->migrator->add('legal.managing_director', null);
        $this->migrator->add('legal.registration_court', null);
        $this->migrator->add('legal.imprint_url', null);
        $this->migrator->add('legal.privacy_policy_url', null);
        $this->migrator->add('legal.terms_url', null);
        $this->migrator->add('legal.cookie_policy_url', null);

        $this->migrator->add('website_defaults.default_support_cta_label', null);
        $this->migrator->add('website_defaults.default_reply_to_email', null);
        $this->migrator->add('website_defaults.default_locale', null);
        $this->migrator->add('website_defaults.fallback_locale', null);
        $this->migrator->add('website_defaults.default_timezone', null);
        $this->migrator->add('website_defaults.default_currency', null);
        $this->migrator->add('website_defaults.default_date_format', null);
        $this->migrator->add('website_defaults.default_time_format', null);
    }
};
