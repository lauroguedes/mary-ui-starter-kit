@php
    $layout = config('app.appearance.login_layout');
@endphp

<x-dynamic-component :component="'layouts.auth.' . $layout" :title="$title ?? null">
    {{ $slot }}
</x-dynamic-component>
