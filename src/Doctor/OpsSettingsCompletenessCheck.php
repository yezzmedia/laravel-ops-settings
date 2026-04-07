<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Doctor;

use YezzMedia\Foundation\Data\DoctorResult;
use YezzMedia\Foundation\Doctor\DoctorCheck;
use YezzMedia\OpsSettings\Support\OpsSettingsManager;

final readonly class OpsSettingsCompletenessCheck implements DoctorCheck
{
    public function __construct(
        private OpsSettingsManager $manager,
    ) {}

    public function key(): string
    {
        return 'settings_completeness';
    }

    public function package(): string
    {
        return 'yezzmedia/laravel-ops-settings';
    }

    public function run(): DoctorResult
    {
        $missing = $this->manager->missingRequiredFields();

        if ($missing === []) {
            return new DoctorResult(
                key: $this->key(),
                package: $this->package(),
                status: 'passed',
                message: 'Required ops settings fields are complete.',
                isBlocking: false,
                context: [
                    'completion_percent' => $this->manager->completionPercent(),
                ],
            );
        }

        return new DoctorResult(
            key: $this->key(),
            package: $this->package(),
            status: 'warning',
            message: 'Some required ops settings fields are still missing.',
            isBlocking: false,
            context: [
                'missing_required_fields' => $missing,
                'completion_percent' => $this->manager->completionPercent(),
            ],
        );
    }
}
