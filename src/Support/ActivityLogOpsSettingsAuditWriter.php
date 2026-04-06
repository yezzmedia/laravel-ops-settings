<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use Spatie\Activitylog\Support\ActivityLogger;
use YezzMedia\OpsSettings\Contracts\OpsSettingsAuditWriter;

final class ActivityLogOpsSettingsAuditWriter implements OpsSettingsAuditWriter
{
    public function __construct(
        private readonly ActivityLogger $logger,
    ) {}

    public function write(string $eventKey, array $context = []): void
    {
        $this->logger
            ->useLog('ops_settings')
            ->event($eventKey)
            ->withProperties($context)
            ->log($eventKey);
    }
}
