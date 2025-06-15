<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen font-sans antialiased bg-base-200">
    {{-- NAVBAR mobile only --}}
    <x-mary-nav sticky class="lg:hidden">
        <x-slot:brand>
            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse"
                wire:navigate>
                <x-app-logo />
            </a>
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden">
                <x-mary-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-mary-nav>

    {{-- MAIN --}}
    <x-mary-main full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100">

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

        {{-- The `$slot` goes here --}}
        <x-slot:content class="flex flex-col min-h-screen">
            <div class="flex-1 flex flex-col items-stretch gap-2">
                {{ $slot }}
            </div>
            <x-partials.footer-info />
        </x-slot:content>
    </x-mary-main>

    {{-- Toast --}}
    <x-mary-toast />
</body>

</html>
