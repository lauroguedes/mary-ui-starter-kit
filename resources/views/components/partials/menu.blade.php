<x-mary-menu {{ $attributes }} activate-by-route>
    @can('dashboard.view')
    <x-mary-menu-item title="Dashboard" icon="m-rectangle-group" :link="route('dashboard')" />
    @endcan

    @can('user.list')
    <x-mary-menu-item title="Users" icon="s-users" :link="route('users.index')" />
    @endcan

    @can('role.list')
    <x-mary-menu-item title="Roles" icon="fas.shield-alt" :link="route('roles.index')" />
    @endcan
</x-mary-menu>
