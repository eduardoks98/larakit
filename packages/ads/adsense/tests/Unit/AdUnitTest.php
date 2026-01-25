<?php

use Eduardoks98\AdsAdsense\Models\AdUnit;
use Eduardoks98\AdsAdsense\Enums\AdFormat;

beforeEach(function () {
    $this->artisan('migrate', ['--database' => 'testing']);
});

it('can create an ad unit', function () {
    $adUnit = AdUnit::create([
        'name' => 'Test Banner',
        'slot_id' => '1234567890',
        'format' => AdFormat::LEADERBOARD,
        'position' => 'header',
        'is_active' => true,
    ]);

    expect($adUnit)->toBeInstanceOf(AdUnit::class)
        ->and($adUnit->name)->toBe('Test Banner')
        ->and($adUnit->slot_id)->toBe('1234567890')
        ->and($adUnit->format)->toBe(AdFormat::LEADERBOARD)
        ->and($adUnit->is_active)->toBeTrue();
});

it('can scope to active ad units', function () {
    AdUnit::create(['name' => 'Active', 'slot_id' => '111', 'is_active' => true]);
    AdUnit::create(['name' => 'Inactive', 'slot_id' => '222', 'is_active' => false]);

    $active = AdUnit::active()->get();

    expect($active)->toHaveCount(1)
        ->and($active->first()->name)->toBe('Active');
});

it('can scope to global ad units', function () {
    AdUnit::create(['name' => 'Global', 'slot_id' => '111', 'game_id' => null]);
    AdUnit::create(['name' => 'Game', 'slot_id' => '222', 'game_id' => 1]);

    $global = AdUnit::global()->get();

    expect($global)->toHaveCount(1)
        ->and($global->first()->name)->toBe('Global');
});

it('can scope by position', function () {
    AdUnit::create(['name' => 'Header', 'slot_id' => '111', 'position' => 'header']);
    AdUnit::create(['name' => 'Sidebar', 'slot_id' => '222', 'position' => 'sidebar']);

    $header = AdUnit::atPosition('header')->get();

    expect($header)->toHaveCount(1)
        ->and($header->first()->name)->toBe('Header');
});

it('gets ad client from config', function () {
    $adUnit = AdUnit::create(['name' => 'Test', 'slot_id' => '111']);

    expect($adUnit->ad_client)->toBe('ca-pub-test123');
});

it('generates html output', function () {
    $adUnit = AdUnit::create([
        'name' => 'Test',
        'slot_id' => '111',
        'format' => AdFormat::RECTANGLE,
    ]);

    $html = $adUnit->toHtml();

    expect($html)
        ->toContain('adsbygoogle')
        ->toContain('data-ad-client="ca-pub-test123"')
        ->toContain('data-ad-slot="111"');
});

it('generates api array output', function () {
    $adUnit = AdUnit::create([
        'name' => 'Test',
        'slot_id' => '111',
        'format' => AdFormat::RESPONSIVE,
        'position' => 'header',
    ]);

    $array = $adUnit->toApiArray();

    expect($array)
        ->toHaveKeys(['id', 'name', 'slot_id', 'format', 'position', 'ad_client', 'is_responsive'])
        ->and($array['is_responsive'])->toBeTrue();
});

it('detects responsive format', function () {
    $responsive = AdUnit::create(['name' => 'R', 'slot_id' => '1', 'format' => AdFormat::RESPONSIVE]);
    $fixed = AdUnit::create(['name' => 'F', 'slot_id' => '2', 'format' => AdFormat::RECTANGLE]);

    expect($responsive->isResponsive())->toBeTrue()
        ->and($fixed->isResponsive())->toBeFalse();
});
