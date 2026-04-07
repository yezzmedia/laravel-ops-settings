<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Doctor;

use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorCheck;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

final readonly class OpsSettingsConsistencyCheck implements DoctorCheck
{
    public function __construct(
        private OpsSettingsManager $manager,
    ) {}

    public function key(): string
    {
        return 'settings_consistency';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-ops-settings';
    }

    public function run(): DoctorResult
    {
        $issues = [];
        $contact = $this->manager->contact();
        $defaults = $this->manager->websiteDefaults();

        if (filled($contact->support_email) && filled($contact->noreply_email) && $contact->support_email === $contact->noreply_email) {
            $issues[] = 'support_email and noreply_email should not be the same address';
        }

        if (filled($defaults->default_locale) && filled($defaults->fallback_locale) && $defaults->default_locale === $defaults->fallback_locale) {
            $issues[] = 'default_locale and fallback_locale should not be identical when both are present';
        }

        if ($issues === []) {
            return new DoctorResult(
                key: $this->key(),
                package: $this->package(),
                status: 'passed',
                message: 'Ops settings defaults are internally consistent.',
                isBlocking: false,
            );
        }

        return new DoctorResult(
            key: $this->key(),
            package: $this->package(),
            status: 'warning',
            message: 'Ops settings contain consistency issues that should be reviewed.',
            isBlocking: false,
            context: ['issues' => $issues],
        );
    }
}
