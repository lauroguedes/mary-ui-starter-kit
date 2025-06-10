<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="mt-4 flex flex-col gap-6">
    <x-mary-alert :title="__('Please verify your email address by clicking on the link we just emailed to you.')" icon="o-exclamation-triangle" />

    @if (session('status') == 'verification-link-sent')
        <x-mary-alert :title="__('A new verification link has been sent to the email address you provided during registration.')" icon="c-check" class="alert-success" />
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <x-mary-button wire:click="sendVerification" :label="__('Resend verification email')" class="btn-accent" />

        <x-mary-button :label="__(key: 'Log out')" wire:click="logout" class="btn-link link-accent link-hover" />
    </div>
</div>
