@props([
    'size' => 'lg',
    'heading' => null,
    'subheading' => null,
    'separator' => false,
])

<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    <h2 class="text-{{ $size }} font-bold">{{ $heading ?? '' }}</h2>
    <p class="text-{{ $size == 'lg' ? 'sm' : 'xs' }} text-base-content/70">{{ $subheading ?? '' }}</p>
    @if ($separator)
        <x-mary-menu-separator />
    @endif
</div>
