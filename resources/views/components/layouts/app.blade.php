@php
    $layout = config('app.appearance.app_layout');
@endphp

<x-dynamic-component :component="'layouts.app.' . $layout" :title="$pageTitle ?? null">
    {{ $slot }}
</x-dynamic-component>
