<?php

use Eduardoks98\AdsAdsense\Models\AdUnit;
use Eduardoks98\AdsAdsense\Enums\AdFormat;
use Eduardoks98\AdsAdsense\Services\AdsenseService;

beforeEach(function () {
    $this->artisan('migrate', ['--database' => 'testing']);
    $this->service = app(AdsenseService::class);
});

it('checks if adsense is enabled', function () {
    expect($this->service->isEnabled())->toBeTrue();
});

it('checks if adsense is configured', function () {
    expect($this->service->isConfigured())->toBeTrue();
});

it('gets publisher id', function () {
    expect($this->service->getPublisherId())->toBe('ca-pub-test123');
});

it('gets ad units for game', function () {
    AdUnit::create(['name' => 'Global', 'slot_id' => '1', 'game_id' => null, 'is_active' => true]);
    AdUnit::create(['name' => 'Game 1', 'slot_id' => '2', 'game_id' => 1, 'is_active' => true]);
    AdUnit::create(['name' => 'Game 2', 'slot_id' => '3', 'game_id' => 2, 'is_active' => true]);

    $units = $this->service->getAdUnitsForGame(1);

    expect($units)->toHaveCount(2);
});

it('gets ad unit by position', function () {
    AdUnit::create(['name' => 'Header', 'slot_id' => '1', 'position' => 'header', 'is_active' => true]);
    AdUnit::create(['name' => 'Sidebar', 'slot_id' => '2', 'position' => 'sidebar', 'is_active' => true]);

    $unit = $this->service->getAdUnitByPosition('header');

    expect($unit)->not->toBeNull()
        ->and($unit->name)->toBe('Header');
});

it('returns null for missing position', function () {
    $unit = $this->service->getAdUnitByPosition('nonexistent');

    expect($unit)->toBeNull();
});

it('gets global ad units', function () {
    AdUnit::create(['name' => 'Global 1', 'slot_id' => '1', 'game_id' => null, 'is_active' => true]);
    AdUnit::create(['name' => 'Global 2', 'slot_id' => '2', 'game_id' => null, 'is_active' => true]);
    AdUnit::create(['name' => 'Game', 'slot_id' => '3', 'game_id' => 1, 'is_active' => true]);

    $units = $this->service->getGlobalAdUnits();

    expect($units)->toHaveCount(2);
});

it('gets ad units grouped by position', function () {
    AdUnit::create(['name' => 'H1', 'slot_id' => '1', 'position' => 'header', 'is_active' => true]);
    AdUnit::create(['name' => 'H2', 'slot_id' => '2', 'position' => 'header', 'is_active' => true]);
    AdUnit::create(['name' => 'S1', 'slot_id' => '3', 'position' => 'sidebar', 'is_active' => true]);

    $grouped = $this->service->getAdUnitsGroupedByPosition();

    expect($grouped)->toHaveKeys(['header', 'sidebar'])
        ->and($grouped['header'])->toHaveCount(2)
        ->and($grouped['sidebar'])->toHaveCount(1);
});

it('creates ad unit', function () {
    $unit = $this->service->createAdUnit([
        'name' => 'New Ad',
        'slot_id' => '12345',
        'format' => AdFormat::RECTANGLE,
    ]);

    expect($unit)->toBeInstanceOf(AdUnit::class)
        ->and($unit->name)->toBe('New Ad');
});

it('updates ad unit', function () {
    $unit = AdUnit::create(['name' => 'Original', 'slot_id' => '1']);

    $updated = $this->service->updateAdUnit($unit, ['name' => 'Updated']);

    expect($updated->name)->toBe('Updated');
});

it('toggles ad unit', function () {
    $unit = AdUnit::create(['name' => 'Test', 'slot_id' => '1', 'is_active' => true]);

    $toggled = $this->service->toggleAdUnit($unit);

    expect($toggled->is_active)->toBeFalse();
});

it('generates script tag', function () {
    $script = $this->service->getScriptTag();

    expect($script)
        ->toContain('pagead2.googlesyndication.com')
        ->toContain('ca-pub-test123');
});

it('renders ad unit html', function () {
    AdUnit::create([
        'name' => 'Header',
        'slot_id' => '12345',
        'position' => 'header',
        'is_active' => true,
    ]);

    $html = $this->service->renderAdUnit('header');

    expect($html)
        ->toContain('adsbygoogle')
        ->toContain('12345');
});

it('returns empty string for disabled adsense', function () {
    config(['adsense.enabled' => false]);
    $service = new AdsenseService();

    $html = $service->renderAdUnit('header');

    expect($html)->toBe('');
});
