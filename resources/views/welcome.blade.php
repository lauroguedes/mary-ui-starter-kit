<x-layouts::guest>
    <div class="flex w-full max-w-[335px] flex-col-reverse lg:max-w-4xl lg:flex-row">
        {{-- Content --}}
        <div class="flex-1 rounded-b-xl bg-base-100 p-6 shadow-sm lg:rounded-s-xl lg:rounded-br-none lg:p-20">
            <h1 class="mb-1 text-lg font-semibold text-base-content">Let's get started</h1>
            <p class="mb-4 text-sm text-base-content/60">
                Build beautiful apps faster with <strong>Mary UI, DaisyUI and Laravel</strong>. Everything you need to ship your next idea.
            </p>

            <ul class="mb-6 flex flex-col gap-3">
                <li class="flex items-center gap-3">
                    <x-mary-icon name="o-book-open" class="size-5 text-primary" />
                    <span class="text-sm">
                        Read the
                        <a href="https://laravel.com/docs" target="_blank" class="link link-primary ms-1">Documentation</a>
                    </span>
                </li>
                <li class="flex items-center gap-3">
                    <x-mary-icon name="o-play-circle" class="size-5 text-primary" />
                    <span class="text-sm">
                        Watch
                        <a href="https://laracasts.com" target="_blank" class="link link-primary ms-1">Laracasts</a>
                    </span>
                </li>
                <li class="flex items-center gap-3">
                    <x-mary-icon name="o-sparkles" class="size-5 text-primary" />
                    <span class="text-sm">
                        Explore
                        <a href="https://mary-ui.com" target="_blank" class="link link-primary ms-1">Mary UI</a>
                    </span>
                </li>
                <li class="flex items-center gap-3">
                    <x-mary-icon name="o-swatch" class="size-5 text-primary" />
                    <span class="text-sm">
                        Style with
                        <a href="https://daisyui.com/docs" target="_blank" class="link link-primary ms-1">DaisyUI</a>
                    </span>
                </li>
                <li class="flex items-center gap-3">
                    <x-mary-icon name="o-code-bracket" class="size-5 text-primary" />
                    <span class="text-sm">
                        Fork on
                        <a href="https://github.com/lauroguedes/mary-ui-starter-kit" target="_blank" class="link link-primary ms-1">GitHub</a>
                    </span>
                </li>
            </ul>

            <x-mary-button
                label="Get started"
                link="{{ route('login') }}"
                icon="o-arrow-right-circle"
                class="btn-primary btn-sm"
            />
        </div>

        {{-- Decorative --}}
        <div class="relative flex aspect-video items-center justify-center overflow-hidden rounded-t-xl bg-primary/10 lg:aspect-auto lg:w-[380px] lg:rounded-tl-none lg:rounded-e-xl">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-primary/10" />
            <div class="relative flex flex-col items-center gap-3">
                <x-app-logo-icon class="size-24 text-primary" />
                <span class="text-lg font-semibold text-base-content">{{ config('app.name', 'Laravel') }}</span>
                <x-mary-button
                    label="Star me on Github"
                    link="https://github.com/lauroguedes/mary-ui-starter-kit"
                    external
                    icon="s-star"
                    class="btn-warning btn-outline btn-sm"
                />
            </div>
        </div>
    </div>
</x-layouts::guest>
