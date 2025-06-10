<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $passwordRules = app()->isProduction() ? ['required', 'string', 'confirmed', Rules\Password::defaults()] : ['required', 'string', 'confirmed'];

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => $passwordRules,
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <x-mary-input :label="__('Name')" wire:model="name" :placeholder="__('Full name')" type="text" required autofocus
            autocomplete="name" />

        <x-mary-input :label="__('Email address')" wire:model="email" placeholder="email@example.com" type="email" required
            autocomplete="email" />

        <x-mary-password wire:model="password" :placeholder="__('Password')" :label="__('Password')" required right
            autocomplete="new-password" />

        <x-mary-password wire:model="password_confirmation" :placeholder="__('Confirm password')" :label="__('Confirm password')" required right
            autocomplete="new-password" />

        <x-mary-button type="submit" :label="__('Create account')" class="btn-accent" />
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-base-content">
        {{ __('Already have an account?') }}
        <x-mary-button :label="__('Log in')" :link="route('login')" class="btn-link link-accent link-hover pl-0" />
    </div>
</div>
