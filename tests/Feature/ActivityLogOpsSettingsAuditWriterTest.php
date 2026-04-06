<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use YezzMedia\OpsSettings\Contracts\OpsSettingsAuditWriter;
use YezzMedia\OpsSettings\Support\ActivityLogOpsSettingsAuditWriter;

it('persists normalized ops settings audit records through activitylog', function (): void {
    if (! class_exists(Activity::class)) {
        $this->markTestSkipped('spatie/laravel-activitylog is not installed in the package environment.');
    }

    if (! Schema::hasTable('activity_log')) {
        Schema::create('activity_log', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->string('event')->nullable();
            $table->json('attribute_changes')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->timestamps();
            $table->index('log_name');
        });
    }

    config()->set('ops-settings.audit.driver', 'activitylog');
    app()->forgetInstance(OpsSettingsAuditWriter::class);

    $writer = app(OpsSettingsAuditWriter::class);

    expect($writer)->toBeInstanceOf(ActivityLogOpsSettingsAuditWriter::class);

    $writer->write('ops.settings.updated', [
        'group' => 'identity',
        'changed_keys' => ['name'],
        'actor_id' => 7,
        'old_values' => ['name' => 'Old Name'],
        'new_values' => ['name' => 'New Name'],
        'source' => 'ops_panel',
    ]);

    $activity = Activity::query()->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity?->log_name)->toBe('ops_settings')
        ->and($activity?->event)->toBe('ops.settings.updated')
        ->and($activity?->description)->toBe('ops.settings.updated')
        ->and($activity?->getProperty('group'))->toBe('identity')
        ->and($activity?->getProperty('actor_id'))->toBe(7)
        ->and($activity?->getProperty('old_values'))->toBe(['name' => 'Old Name'])
        ->and($activity?->getProperty('new_values'))->toBe(['name' => 'New Name']);
});
