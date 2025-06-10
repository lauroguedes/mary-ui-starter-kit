<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen font-sans antialiased bg-base-200">
    <x-mary-nav sticky full-width>
        {{-- BRAND --}}
        <x-slot:brand>
            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>
        </x-slot:brand>

        {{-- MENU AND ACTIONS --}}
        <x-slot:actions>
            {{-- MENU --}}
            <div class="hidden lg:flex items-center justify-end gap-4 rtl:space-x-reverse">
                <x-partials.menu class="menu-horizontal space-x-2 !p-0" />
                <livewire:settings.user-menu />
                <x-mary-theme-toggle />
            </div>

            {{-- MOBILE TRIGGER BUTTON --}}
            <label for="main-drawer" class="lg:hidden">
                <x-mary-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-mary-nav>

    <x-mary-main full-width>
        {{-- SIDEBAR MOBILE ONLY --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:hidden">

            {{-- BRAND --}}
            <div class="flex justify-between items-center m-3">
                <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse"
                    wire:navigate>
                    <x-app-logo />
                </a>
                <x-mary-theme-toggle />
            </div>

            {{-- USER MENU --}}
            <div class="mx-3">
                <x-mary-menu-separator />
                <livewire:settings.user-menu />
                <x-mary-menu-separator />
            </div>

            {{-- MENU --}}
            <x-partials.menu />
        </x-slot:sidebar>

        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-mary-main>
</body>

</html>
