<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('password can be updated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret'),
    ]);

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    $response = Volt::test('settings.password')
        ->set('current_password', 'secret')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasNoErrors();

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret'),
    ]);

    $user->givePermissionTo('profile.update');

    $this->actingAs($user);

    $response = Volt::test('settings.password')
        ->set('current_password', 'wrong-password')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    $response->assertHasErrors(['current_password']);
});

test('users without profile update permission cannot update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('secret'),
    ]);

    $this->actingAs($user);

    $response = Volt::test('settings.password')
        ->set('current_password', 'secret')
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword');

    // Verify password was not updated
    expect(Hash::check('secret', $user->refresh()->password))->toBeTrue()
        ->and(Hash::check('new-password', $user->password))->toBeFalse();
});
