<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;

new class extends Component {
    use Toast, WithFileUploads;

    public User $user;

    #[Rule('required|max:100')]
    public string $name = '';

    #[Rule('required|email|max:50')]
    public string $email = '';

    #[Rule('nullable|image|max:1024')]
    public $avatar = '';

    public function mount(): void
    {
        $this->fill($this->user);
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->user->update($data);

        if ($this->avatar) {
            $url = $this->avatar->store('users', 'public');
            $this->user->update(['avatar' => "/storage/$url"]);
        }

        $this->success('User updated with success.', redirectTo: route('users.index'));
    }

}; ?>

<x-pages.layout :page-title="__('Update') . ' - ' . $user->name">
    <x-slot:content>
        <div class="grid gap-5 lg:grid-cols-2">
            <x-mary-form wire:submit="save">
                <x-mary-file wire:model="avatar" accept="image/png, image/jpeg" crop-after-change>
                    <img src="{{ $user->avatar ?? '/images/empty-user.jpg' }}" class="h-36 rounded-lg" />
                </x-mary-file>

                <x-mary-input :label="__('Name')" wire:model="name" />
                <x-mary-input :label="__('Email')" wire:model="email" />

                <x-slot:actions>
                    <x-mary-button :label="__('Cancel')" :link="route('users.index')" class="btn-soft" />
                    <x-mary-button :label="__('Save')" icon="o-paper-airplane" spinner="save" type="submit"
                        class="btn-primary" />
                </x-slot:actions>
            </x-mary-form>
            <div class="hidden lg:block place-self-center">
                <img src="/images/user-action-page.svg" width="300" class="mx-auto" />
            </div>
        </div>
    </x-slot:content>
</x-pages.layout>

@push('scripts')
    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endpush
