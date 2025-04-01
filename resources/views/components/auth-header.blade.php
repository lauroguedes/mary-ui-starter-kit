@props(['title', 'description'])

<div class="flex w-full flex-col text-center">
    <x-mary-header title="{{ $title }}" size="text-xl" subtitle="{{ $description }}"
        class="flex justify-center !my-3" />
</div>
