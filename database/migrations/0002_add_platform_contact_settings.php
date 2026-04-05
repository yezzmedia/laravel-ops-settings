<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contact.support_email', null);
        $this->migrator->add('contact.contact_phone', null);
        $this->migrator->add('contact.address_line_1', null);
        $this->migrator->add('contact.address_line_2', null);
        $this->migrator->add('contact.postal_code', null);
        $this->migrator->add('contact.city', null);
        $this->migrator->add('contact.country_code', null);
    }
};
