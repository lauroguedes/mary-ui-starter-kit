<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Attributes\Rule;

new class extends Component {
    use Toast;

    public User $user;

    #[Rule('required|max:100')]
    public string $name = '';

    #[Rule('required|email|max:50')]
    public string $email = '';

    public function mount(): void
    {
        $this->fill($this->user);
    }

    public function save(): void
    {
        $data = $this->validate();

        $this->user->update($data);

        $this->success('User updated with success.', redirectTo: route('users.index'));
    }

}; ?>

<x-pages.layout :page-title="__('Update') . ' - ' . $user->name">
    <x-slot:content>
        <x-mary-form wire:submit="save">
            <x-mary-input :label="__('Name')" wire:model="name" />
            <x-mary-input :label="__('Email')" wire:model="email" />

            <x-slot:actions>
                <x-mary-button :label="__('Cancel')" :link="route('users.index')" class="btn-soft" />
                <x-mary-button :label="__('Save')" icon="o-paper-airplane" spinner="save" type="submit"
                    class="btn-primary" />
            </x-slot:actions>
        </x-mary-form>
    </x-slot:content>
</x-pages.layout>
