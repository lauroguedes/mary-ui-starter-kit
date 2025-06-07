@props(['title', 'description'])

<div class="flex w-full flex-col items-center">
    <h1 class="text-xl font-bold">{{ $title }}</h1>
    <p class="text-base-content/50 text-sm mt-1">{{ $description }}</p>
</div>
