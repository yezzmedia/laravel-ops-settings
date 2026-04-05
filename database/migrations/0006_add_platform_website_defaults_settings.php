<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('website_defaults.default_site_title_pattern', null);
        $this->migrator->add('website_defaults.default_footer_label', null);
        $this->migrator->add('website_defaults.default_support_label', null);
    }
};
