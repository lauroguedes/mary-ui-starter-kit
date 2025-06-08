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
    <x-mary-menu-separator />

    <x-mary-list-item :item="$user" value="name" sub-value="email" no-separator no-hover
        class="-mx-2 !-my-2 rounded">
        <x-slot:avatar>
            <x-mary-avatar :placeholder="$user->initials()" class="!w-10" />
        </x-slot:avatar>
        <x-slot:actions>
            <x-mary-dropdown>
                <x-slot:trigger>
                    <x-mary-button icon="m-cog-6-tooth" class="btn-circle" />
                </x-slot:trigger>
                <x-mary-menu-item :title="__('Profile')" icon="c-user" :link="route('settings.profile')" />
                <x-mary-menu-item :title="__('Repository')" icon="fab.github" link="https://laravel.com/docs/starter-kits"
                    external />
                <x-mary-menu-item :title="__('Documentation')" icon="s-book-open" link="https://laravel.com/docs/starter-kits"
                    external />
                <x-mary-menu-item :title="__('Log out')" wire:click.stop="logout" spinner="logout" class="text-error"
                    icon="o-power" />
            </x-mary-dropdown>
        </x-slot:actions>
    </x-mary-list-item>

    <x-mary-menu-separator />
</div>
