@props([
    'size' => 'lg',
    'heading' => null,
    'subheading' => null,
    'divider' => false,
])

<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    <h2 class="text-{{ $size }} font-bold">{{ $heading ?? '' }}</h2>
    <p class="text-{{ $size == 'lg' ? 'sm' : 'xs' }} text-base-content/70">{{ $subheading ?? '' }}</p>
    @if ($divider)
        <div class="divider my-2"></div>
    @endif
</div>
