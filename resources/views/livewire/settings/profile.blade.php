<?php

use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    use WithFileUploads;

    public User $user;

    #[Validate('required|string|max:100')]
    public string $name = '';

    public string $email = '';

    #[Validate('nullable')]
    public mixed $avatar = null;

    public function mount(): void
    {
        $this->user = auth()->user();

        $this->name = $this->user->name;
        $this->email = $this->user->email;
    }

    protected function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user->id)
            ]
        ];
    }

    public function updateProfileInformation(): void
    {
        $validated = $this->validate();

        $this->processUpload($validated);

        $this->user->fill($validated);

        if ($this->user->isDirty('email')) {
            $this->user->email_verified_at = null;
        }

        $this->user->save();

        $this->dispatch('profile-updated', user: $this->user);
    }

    public function resendVerificationNotification(): void
    {
        if ($this->user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $this->user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    private function processUpload(array &$validated): void
    {
        if (!$this->avatar || !($this->avatar instanceof \Illuminate\Http\UploadedFile)) {
            return;
        }

        $this->validate([
            'avatar' => 'image|max:1024'
        ]);

        if ($this->user->avatar) {
            $path = str($this->user->avatar)->after('/storage/');
            \Storage::disk('public')->delete($path);
        }

        $url = $this->avatar->store('users', 'public');
        $validated['avatar'] = "/storage/{$url}";
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="w-full space-y-6">
            <x-mary-file wire:model="avatar" accept="image/png, image/jpeg" crop-after-change>
                <img src="{{ $user->avatar ?? '/images/empty-user.jpg' }}" class="h-24 rounded-lg" />
            </x-mary-file>
            <x-mary-input :label="__('Name')" wire:model="name" required autofocus autocomplete="name" />
            <x-mary-input :label="__('Email address')" wire:model="email" type="email" required autocomplete="email" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !auth()->user()->hasVerifiedEmail())
                <div class="space-y-4">
                    <x-mary-alert :title="__('Your email address is unverified.')" :description="__('Re-send the verification email.')" icon="o-exclamation-triangle" class="alert-info alert-soft">
                        <x-slot:actions>
                            <x-mary-button wire:click.prevent="resendVerificationNotification" :label="__('Re-send email')" />
                        </x-slot:actions>
                    </x-mary-alert>

                    @if (session('status') === 'verification-link-sent')
                        <x-mary-alert :title="__('A new verification link has been sent to your email address.')" icon="s-check"
                            class="alert-success alert-soft" />
                    @endif
                </div>
            @endif

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <x-mary-button type="submit" :label="__('Save')" class="btn-accent" />
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>

@push('scripts')
    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endpush
