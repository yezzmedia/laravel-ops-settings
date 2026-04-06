<?php

declare(strict_types=1);

use Spatie\Activitylog\ActivitylogServiceProvider;
use YezzMedia\OpsSettings\Doctor\OpsSettingsAuditConfiguredCheck;

it('warns when ops audit is active but ops settings audit is not configured', function (): void {
    config()->set('ops.integrations.audit.provider', ActivitylogServiceProvider::class);
    config()->set('ops-settings.audit.driver', null);

    $result = app(OpsSettingsAuditConfiguredCheck::class)->run();

    expect($result->status)->toBe('warning')
        ->and($result->isBlocking)->toBeFalse()
        ->and($result->message)->toBe('Ops settings audit events are not persisted because ops-settings.audit.driver is not configured.');
});

it('passes when ops settings audit persistence is configured', function (): void {
    config()->set('ops.integrations.audit.provider', ActivitylogServiceProvider::class);
    config()->set('ops-settings.audit.driver', 'activitylog');

    $result = app(OpsSettingsAuditConfiguredCheck::class)->run();

    expect($result->status)->toBe('passed')
        ->and($result->isBlocking)->toBeFalse();
});

it('skips when ops audit is not configured for ops settings', function (): void {
    config()->set('ops.integrations.audit.provider', null);
    config()->set('ops-settings.audit.driver', 'activitylog');

    $result = app(OpsSettingsAuditConfiguredCheck::class)->run();

    expect($result->status)->toBe('skipped')
        ->and($result->isBlocking)->toBeFalse();
});
