<div>
    <x-mary-header class="!mb-6" :title="$title ?? null" :subtitle="$subtitle ?? null" separator>
        @isset($search)
            <x-slot:middle class="!justify-end">
                {{ $search }}
            </x-slot:middle>
        @endisset
        @isset($actions)
            <x-slot:actions>
                {{ $actions }}
            </x-slot:actions>
        @endisset
    </x-mary-header>
    <div>
        {{ $slot }}
    </div>
</div>