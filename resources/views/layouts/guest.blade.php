<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    @livewireStyles
</head>

<body class="flex min-h-screen flex-col bg-base-200 antialiased">
    <x-mary-nav sticky full-width>
        <x-slot:brand>
            <a href="{{ route('home') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse">
                <x-app-logo />
            </a>
        </x-slot:brand>

        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-mary-theme-toggle />

                @auth
                    <x-mary-button label="Dashboard" link="{{ route('dashboard') }}" class="btn-primary btn-sm" />
                @else
                    <x-mary-button label="Log in" link="{{ route('login') }}" class="btn-ghost btn-sm" />

                    @if (Route::has('register'))
                        <x-mary-button label="Register" link="{{ route('register') }}" class="btn-primary btn-sm" />
                    @endif
                @endauth
            </div>
        </x-slot:actions>
    </x-mary-nav>

    <main class="flex flex-1 items-center justify-center p-6 lg:p-8">
        {{ $slot }}
    </main>

    <x-partials.footer-info class="p-6" />

    @livewireScripts
</body>

</html>
