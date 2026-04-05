<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('social.facebook_url', null);
        $this->migrator->add('social.instagram_url', null);
        $this->migrator->add('social.linkedin_url', null);
        $this->migrator->add('social.x_url', null);
        $this->migrator->add('social.youtube_url', null);
    }
};
