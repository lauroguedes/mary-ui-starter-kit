<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified', 'can-login'])->group(function () {
    Route::view('dashboard', 'dashboard')
        ->can('dashboard.view')
        ->name('dashboard');

    Route::redirect('settings', 'settings/profile')
        ->can('profile.view');

    Route::livewire('settings/profile', 'pages::settings.profile')
        ->can('profile.view')
        ->name('settings.profile');
    Route::livewire('settings/password', 'pages::settings.password')
        ->can('profile.password')
        ->name('settings.password');

    Route::prefix('users')->name('users.')->group(function () {
        Route::livewire('/', 'pages::users.index')
            ->can('viewAny', User::class)
            ->name('index');
        Route::livewire('/create', 'pages::users.create')
            ->can('create', User::class)
            ->name('create');
        Route::livewire('/{user}/edit', 'pages::users.edit')
            ->can('view', 'user')
            ->name('edit');
    });

    Route::prefix('roles')->name('roles.')->group(function () {
        Route::livewire('/', 'pages::roles.index')
            ->can('role.list')
            ->name('index');
        Route::livewire('/create', 'pages::roles.create')
            ->can('role.create')
            ->name('create');
        Route::livewire('/{role}/edit', 'pages::roles.edit')
            ->can('role.view')
            ->name('edit');
    });

    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::livewire('/', 'pages::permissions.index')
            ->can('permission.list')
            ->name('index');
        Route::livewire('/create', 'pages::permissions.create')
            ->can('permission.create')
            ->name('create');
        Route::livewire('/{permission}/edit', 'pages::permissions.edit')
            ->can('permission.view')
            ->name('edit');
    });
});

require __DIR__ . '/auth.php';
