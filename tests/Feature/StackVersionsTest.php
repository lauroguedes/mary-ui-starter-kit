<?php

declare(strict_types=1);

use App\Services\StackVersions;
use Composer\InstalledVersions;
use Illuminate\Support\Facades\Cache;

test('stack versions reports the running php and laravel versions', function () {
    $versions = app(StackVersions::class)->all();

    expect($versions)->toHaveKeys(['PHP', 'Laravel'])
        ->and($versions['PHP'])->toStartWith(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION)
        ->and($versions['Laravel'])->toStartWith(mb_substr(app()->version(), 0, 2));
});

test('stack versions reports the installed package versions', function () {
    $versions = app(StackVersions::class)->all();

    expect($versions)->toHaveKeys(['Livewire', 'Mary UI', 'Pest'])
        ->and($versions['Livewire'])->toBe(mb_ltrim((string) InstalledVersions::getPrettyVersion('livewire/livewire'), 'v'))
        ->and($versions['Mary UI'])->toBe(mb_ltrim((string) InstalledVersions::getPrettyVersion('robsontenorio/mary'), 'v'))
        ->and($versions['Pest'])->toBe(mb_ltrim((string) InstalledVersions::getPrettyVersion('pestphp/pest'), 'v'));
});

test('every reported version is a bare dotted number or a branch reference', function () {
    $versions = app(StackVersions::class)->all();

    foreach ($versions as $label => $version) {
        expect($version)->toMatch(
            '/^(\d+(\.\d+)*|dev-.+)$/',
            "{$label} should be a bare version number or a dev branch reference"
        );
    }
});

test('branch references survive normalization instead of being reduced to digits', function () {
    $service = app(StackVersions::class);
    $method = new ReflectionMethod(StackVersions::class, 'normalize');
    $normalize = fn (string $version): string => $method->invoke($service, $version);

    expect($normalize('dev-main'))->toBe('dev-main')
        ->and($normalize('dev-2.x'))->toBe('dev-2.x')
        ->and($normalize('v4.4.1'))->toBe('4.4.1')
        ->and($normalize('13.26.1-dev'))->toBe('13.26.1')
        ->and($normalize('8.5.8 (cli)'))->toBe('8.5.8');
});

test('stack versions are resolved once and served from cache', function () {
    Cache::forget('stack-versions');

    $service = app(StackVersions::class);
    $first = $service->all();

    expect(Cache::get('stack-versions'))->toBe($first)
        ->and($service->all())->toBe($first);
});

test('welcome page displays the stack versions below the app name', function () {
    $versions = app(StackVersions::class)->all();

    $response = $this->get('/');

    $response->assertSuccessful();

    foreach ($versions as $label => $version) {
        $response->assertSee($label)->assertSee($version);
    }
});
