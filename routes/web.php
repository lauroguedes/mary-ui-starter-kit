<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified', 'can-login'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->can('dashboard.view')
        ->name('dashboard');

    Route::redirect('settings', 'settings/profile')
        ->can('profile.view');

    Volt::route('settings/profile', 'settings.profile')
        ->can('profile.view')
        ->name('settings.profile');
    Volt::route('settings/password', 'settings.password')
        ->can('profile.password')
        ->name('settings.password');

    Route::prefix('users')->name('users.')->group(function () {
        Volt::route('/', 'pages.users.index')
            ->can('user.list')
            ->name('index');
        Volt::route('/create', 'pages.users.create')
            ->can('user.view')
            ->name('create');
        Volt::route('/{user}/edit', 'pages.users.edit')
            ->can('user.view')
            ->name('edit');
        // Add more user routes here as needed
    });
});

require __DIR__ . '/auth.php';
