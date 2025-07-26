<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        $this->authorize('profile.update');

        $passwordRules = app()->isProduction() ? ['required', 'string', 'confirmed', Password::defaults()] : ['required', 'string', 'confirmed'];

        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => $passwordRules,
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }

    public function exception(Throwable $e, $stopPropagation): void
    {
        if ($e instanceof AuthorizationException) {
            $this->error($e->getMessage());

            $stopPropagation();
        }
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Update password')"
                       :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form wire:submit="updatePassword" class="space-y-6">
            <x-mary-password wire:model="current_password" :label="__('Current password')" required right
                             autocomplete="current-password"/>

            <x-mary-password wire:model="password" :label="__('New Password')" required right
                             autocomplete="new-password"/>

            <x-mary-password wire:model="password_confirmation" :label="__('Confirm password')" required right
                             autocomplete="new-password"/>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <x-mary-button type="submit" :label="__('Save')" class="btn-accent"/>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
