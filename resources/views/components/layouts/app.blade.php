@php
    $layout = config('app.appearance.app_layout');
@endphp

<x-dynamic-component :component="'layouts.app.' . $layout" :title="$title ?? null">
    {{ $slot }}
</x-dynamic-component>
