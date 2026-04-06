<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Support;

use YezzMedia\OpsSettings\Contracts\OpsSettingsAuditWriter;

final class NullOpsSettingsAuditWriter implements OpsSettingsAuditWriter
{
    public function write(string $eventKey, array $context = []): void {}
}
