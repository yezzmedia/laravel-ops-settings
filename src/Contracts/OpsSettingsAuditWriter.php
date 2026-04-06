<?php

declare(strict_types=1);

namespace YezzMedia\OpsSettings\Contracts;

interface OpsSettingsAuditWriter
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function write(string $eventKey, array $context = []): void;
}
