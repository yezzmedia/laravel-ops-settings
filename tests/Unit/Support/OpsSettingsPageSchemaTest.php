<?php

declare(strict_types=1);

use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use YezzMedia\OpsSettings\Support\OpsSettingsGroup;
use YezzMedia\OpsSettings\Support\OpsSettingsPageSchema;

it('uses select fields for locale timezone currency and format website defaults', function (): void {
    $sections = OpsSettingsPageSchema::schema(OpsSettingsGroup::WebsiteDefaults);

    expect($sections)->toHaveCount(3)
        ->and($sections[0])->toBeInstanceOf(Section::class);

    $localizationGrid = $sections[0]->getDefaultChildComponents()[0];

    expect($localizationGrid)->toBeInstanceOf(Grid::class);

    $fields = $localizationGrid->getDefaultChildComponents();

    expect($fields[0])->toBeInstanceOf(Select::class)
        ->and($fields[0]->getName())->toBe('default_locale')
        ->and($fields[1])->toBeInstanceOf(Select::class)
        ->and($fields[1]->getName())->toBe('fallback_locale')
        ->and($fields[2])->toBeInstanceOf(Select::class)
        ->and($fields[2]->getName())->toBe('default_timezone')
        ->and($fields[3])->toBeInstanceOf(Select::class)
        ->and($fields[3]->getName())->toBe('default_currency');

    $formattingGrid = $sections[1]->getDefaultChildComponents()[0];

    expect($formattingGrid)->toBeInstanceOf(Grid::class);

    $formatFields = $formattingGrid->getDefaultChildComponents();

    expect($formatFields[0])->toBeInstanceOf(Select::class)
        ->and($formatFields[0]->getName())->toBe('default_date_format')
        ->and($formatFields[1])->toBeInstanceOf(Select::class)
        ->and($formatFields[1]->getName())->toBe('default_time_format');
});

it('provides stable locale timezone currency and format options', function (): void {
    expect(OpsSettingsPageSchema::localeOptions())
        ->toHaveKey('de')
        ->toHaveKey('en');

    expect(OpsSettingsPageSchema::timezoneOptions())
        ->toHaveKey('Europe/Berlin')
        ->toHaveKey('UTC');

    expect(OpsSettingsPageSchema::currencyOptions())
        ->toHaveKey('EUR')
        ->toHaveKey('USD');

    expect(OpsSettingsPageSchema::dateFormatOptions())
        ->toHaveKey('d.m.Y')
        ->toHaveKey('Y-m-d')
        ->and(OpsSettingsPageSchema::dateFormatOptions()['d.m.Y'])->toContain('31.12.2026')
        ->toContain('(d.m.Y)');

    expect(OpsSettingsPageSchema::timeFormatOptions())
        ->toHaveKey('H:i')
        ->toHaveKey('h:i A')
        ->and(OpsSettingsPageSchema::timeFormatOptions()['H:i'])->toContain('17:45')
        ->toContain('(H:i)');
});
