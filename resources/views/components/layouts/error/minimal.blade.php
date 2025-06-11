{{-- Mary UI Minimal Error Layout --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="bg-base-300 min-h-screen flex items-center justify-center">
    <div class="flex flex-col items-center justify-center max-w-md w-full">
        <x-mary-card class="indicator" :title="$title" :subtitle="$message" shadow separator>
            {{ $slot }}
            <x-mary-badge :value="$code" class="badge-error badge-xl indicator-item" />
            <x-mary-button :label="__('Go home')" :link="route('home')" icon="o-home" class="btn-primary mt-2" />
        </x-mary-card>
    </div>
</body>

</html>
