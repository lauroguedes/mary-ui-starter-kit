@inject('stackVersions', App\Services\StackVersions::class)

@php($versions = $stackVersions->all())

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center justify-center gap-x-2 gap-y-1']) }}>
    @foreach ($versions as $label => $version)
        <span class="text-[10px] leading-none text-base-content/40">
            {{ $label }}<span class="ms-1 font-mono text-base-content/60">{{ $version }}</span>
        </span>

        @unless ($loop->last)
            <span class="text-[10px] leading-none text-base-content/20" aria-hidden="true">&middot;</span>
        @endunless
    @endforeach
</div>
