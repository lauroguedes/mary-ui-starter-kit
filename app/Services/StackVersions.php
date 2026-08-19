<?php

declare(strict_types=1);

namespace App\Services;

use Composer\InstalledVersions;
use Illuminate\Support\Facades\Cache;
use OutOfBoundsException;

final class StackVersions
{
    private const string CACHE_KEY = 'stack-versions';

    /**
     * Composer packages to report, keyed by the label shown in the UI.
     *
     * @var array<string, string>
     */
    private const array PACKAGES = [
        'Livewire' => 'livewire/livewire',
        'Mary UI' => 'robsontenorio/mary',
        'Pest' => 'pestphp/pest',
    ];

    /**
     * The versions powering this application, keyed by label.
     *
     * Cached forever because the values can only change on a deploy, which
     * ships a new `composer.lock` and clears the application cache.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => $this->resolve());
    }

    /**
     * @return array<string, string>
     */
    private function resolve(): array
    {
        $versions = [
            'PHP' => $this->normalize(PHP_VERSION),
            'Laravel' => $this->normalize(app()->version()),
        ];

        foreach (self::PACKAGES as $label => $package) {
            $version = $this->packageVersion($package);

            if ($version !== null) {
                $versions[$label] = $version;
            }
        }

        return $versions;
    }

    /**
     * Dev-only packages such as Pest are absent from a `--no-dev` install, so a
     * missing package is expected rather than an error.
     */
    private function packageVersion(string $package): ?string
    {
        if (! InstalledVersions::isInstalled($package)) {
            return null;
        }

        try {
            $version = InstalledVersions::getPrettyVersion($package);
        } catch (OutOfBoundsException) {
            return null;
        }

        return $version === null ? null : $this->normalize($version);
    }

    /**
     * Reduce "v4.4.1", "13.26.1-dev" or "8.5.8 (cli)" to "13.26.1".
     */
    private function normalize(string $version): string
    {
        preg_match('/\d+(\.\d+)*/', $version, $matches);

        return $matches[0] ?? mb_ltrim($version, 'v');
    }
}
