<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('legal.legal_entity_name', null);
        $this->migrator->add('legal.registration_number', null);
        $this->migrator->add('legal.vat_id', null);
        $this->migrator->add('legal.legal_notice_snippet', null);
        $this->migrator->add('legal.privacy_contact_email', null);
    }
};
