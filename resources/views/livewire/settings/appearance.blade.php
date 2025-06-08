<?php

use Livewire\Volt\Component;

new class extends Component {
    public array $darkModeOptions = [
        ['id' => 1, 'name' => 'John'],
        ['id' => 2, 'name' => 'Doe'],
        ['id' => 3, 'name' => 'Mary', 'icon' => true], // <-- This
        ['id' => 4, 'name' => 'Kate'],
    ];
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">

    </x-settings.layout>
</section>
