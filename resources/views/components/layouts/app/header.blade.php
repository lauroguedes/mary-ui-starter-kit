<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen font-sans antialiased bg-base-200">
    <x-mary-nav sticky full-width>
        <x-slot:brand>
            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>
        </x-slot:brand>
        <x-slot:actions>
            <x-partials.menu class="menu-horizontal space-x-2 !p-0" />
            <livewire:settings.user-menu />
            <x-mary-theme-toggle />
        </x-slot:actions>
    </x-mary-nav>
    {{-- <x-daisy.nav-bar>
        <x-slot:brand>
            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>
        </x-slot:brand>
        <x-slot:items>
            <x-mary-menu activate-by-route class="menu-horizontal space-x-2">
                <x-mary-menu-item title="Dashboard" icon="m-rectangle-group" :link="route('dashboard')" />
                <x-mary-menu-item title="Users" icon="s-users" link="/users" />
            </x-mary-menu>
        </x-slot:items>
        <x-slot:actions>
            <livewire:settings.user-menu />
        </x-slot:actions>
    </x-daisy.nav-bar> --}}
    <x-mary-main full-width>
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-mary-main>
</body>

</html>
