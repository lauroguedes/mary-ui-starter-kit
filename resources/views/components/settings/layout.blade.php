<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <x-mary-menu activate-by-route class="!w-64">
            <x-mary-menu-item :title="__('Profile')" icon="m-user" icon-classes="text-primary" :link="route('settings.profile')" />
            <x-mary-menu-item :title="__('Password')" icon="c-finger-print" icon-classes="text-primary" :link="route('settings.password')" />
        </x-mary-menu>
    </div>

    <div class="divider divider-horizontal"></div>

    <div class="flex-1 self-stretch max-md:pt-6">
        <x-partials.header-title :heading="$heading" :subheading="$subheading" separator />

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
