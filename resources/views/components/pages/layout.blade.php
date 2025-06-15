<div>
    <x-mary-header class="!mb-6" :title="$pageTitle ?? null" :subtitle="$pageSubtitle ?? null" separator
        progress-indicator>
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
    @isset($content)
        <x-mary-card shadow>
            {{ $content }}
        </x-mary-card>
    @endisset
    <div>
        {{ $slot }}
    </div>
</div>
