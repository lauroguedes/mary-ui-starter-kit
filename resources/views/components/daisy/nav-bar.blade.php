@props([
    'sticky' => false,
])

<div {{ $attributes->class(['navbar bg-base-100 shadow-sm', 'sticky top-0 z-10' => $sticky]) }}>
    <div class="navbar-start">
        {{ $brand }}
    </div>
    <div class="navbar-center hidden lg:flex">
        {{ $items }}
    </div>
    <div class="navbar-end">
        {{ $actions }}
    </div>
</div>
