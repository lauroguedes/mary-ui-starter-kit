<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public User $user;
}; ?>

<x-pages.layout :title="__('Update') . ' - ' . $user->name">
    update
</x-pages.layout>
