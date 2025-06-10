<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        Password::sendResetLink($this->only('email'));

        session()->flash('status', __('A reset link will be sent if the account exists.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <x-mary-input :label="__('Email address')" wire:model="email" placeholder="email@example.com" type="email" required
            autofocus autocomplete="email" />

        <x-mary-button type="submit" :label="__('Email password reset link')" class="btn-accent" />
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-base-content">
        {{ __('Or, return to') }}
        <x-mary-button :label="__('log in')" :link="route('login')" class="btn-link link-accent link-hover pl-0" />
    </div>
</div>
