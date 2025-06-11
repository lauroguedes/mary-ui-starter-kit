<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-base-200 antialiased">
    <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <div class="flex w-full max-w-md flex-col gap-6">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                <span class="flex h-9 w-9 items-center justify-center rounded-md">
                    <x-app-logo-icon class="size-9 fill-current" />
                </span>

                <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
            </a>

            <div class="flex flex-col gap-6">
                <div class="rounded-xl border bg-base-300 border-base-100 shadow-xs">
                    <div class="px-10 py-8">{{ $slot }}</div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
