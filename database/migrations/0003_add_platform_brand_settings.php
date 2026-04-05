<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('brand.brand_name', null);
        $this->migrator->add('brand.brand_tagline', null);
        $this->migrator->add('brand.primary_color', null);
        $this->migrator->add('brand.secondary_color', null);
        $this->migrator->add('brand.logo_reference', null);
    }
};
