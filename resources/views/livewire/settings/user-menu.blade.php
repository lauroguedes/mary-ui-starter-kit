<?php

use Livewire\Volt\Component;
use App\Livewire\Actions\Logout;
use App\Models\User;
use Livewire\Attributes\On;

new class extends Component {
    public User $user;

    public function mount(): void
    {
        $this->user = auth()->user();
    }

    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }

    #[On('profile-updated')]
    public function onProfileUpdated(User $user): void
    {
        $this->user = $user;
    }
}; ?>
<div>
    <x-mary-dropdown right>
        <x-slot:trigger class="cursor-pointer hover:opacity-80 transition-all">
            <x-mary-avatar :placeholder="$user->initials()" class="!w-10">
                <x-slot:title class="text-sm font-semibold max-w-[150px] truncate">
                    {{ $user->name }}
                </x-slot:title>
                <x-slot:subtitle class="text-xs font-light max-w-[150px] truncate">
                    {{ $user->email }}
                </x-slot:subtitle>
            </x-mary-avatar>
        </x-slot:trigger>
        <x-mary-menu-item :title="__('Profile')" icon="c-user" :link="route('settings.profile')" />
        <x-mary-menu-item :title="__('Repository')" icon="fab.github" link="https://laravel.com/docs/starter-kits" external />
        <x-mary-menu-item :title="__('Documentation')" icon="s-book-open" link="https://laravel.com/docs/starter-kits" external />
        <x-mary-menu-item :title="__('Log out')" wire:click.stop="logout" spinner="logout" class="text-error"
            icon="o-power" />
    </x-mary-dropdown>
    <style>
        .dropdown {
            width: 100%;
        }
    </style>
</div>
