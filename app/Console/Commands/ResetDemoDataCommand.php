<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ResetDemoDataCommand extends Command
{
    protected $signature = 'demo:reset {--force : Force reset even if demo mode is disabled}';

    protected $description = 'Reset all demo data by refreshing migrations and re-seeding the database';

    public function handle(): int
    {
        if (! config('app.demo.enabled') && ! $this->option('force')) {
            $this->error('Demo mode is not enabled. Use --force to override.');

            return self::FAILURE;
        }

        $this->info('Resetting demo data...');

        $newPassword = Str::random(12);
        Cache::put('demo-password', $newPassword);

        Artisan::call('down');
        $this->info('Application is now in maintenance mode.');

        try {
            Artisan::call('migrate:refresh', ['--seed' => true, '--force' => true]);
            $this->info('Database refreshed and re-seeded.');

            Redis::connection(config('session.connection', 'default'))->flushdb();
            $this->info('All active sessions have been revoked.');

            Storage::disk('public')->deleteDirectory('users');
            Storage::deleteDirectory('livewire-tmp');
            $this->info('Uploaded files have been cleaned up.');
        } finally {
            Artisan::call('up');
            $this->info('Application is back online.');
        }

        $this->info('Demo data has been reset successfully.');

        return self::SUCCESS;
    }
}
