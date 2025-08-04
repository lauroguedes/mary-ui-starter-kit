<?php

use App\Traits\ClearsFilters;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

new class extends Component {
    use Toast;
    use WithPagination;
    use ClearsFilters;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public bool $modal = false;

    public mixed $targetDelete = null;

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'permission', 'label' => 'Permission'],
        ];
    }

    public function delete(Permission $permission): void
    {
        $this->authorize('permission.delete');

        throw_if(
            $permission->users->isNotEmpty() || $permission->roles->isNotEmpty(),
            AuthorizationException::class,
            __('Before deleting the permission, remove the roles and users binding.')
        );

        $permission->delete();

        $this->modal = false;

        $this->success(
            __("Permission {$permission->name} has been deleted."),
        );
    }

    public function permissions(): LengthAwarePaginator
    {
        return Permission::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->with(['roles', 'users'])
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'permissions' => $this->permissions(),
            'headers' => $this->headers(),
        ];
    }

    public function exception(Throwable $e, $stopPropagation): void
    {
        if ($e instanceof AuthorizationException) {
            $this->modal = false;
            $this->drawer = false;

            $this->error($e->getMessage());

            $stopPropagation();
        }
    }
}; ?>

<x-pages.layout :page-title="__('Permissions')">
    @can('permission.search')
        <x-slot:search>
            <x-mary-input class="input-sm" :placeholder="__('Search...')" wire:model.live.debounce="search" clearable
                          icon="o-magnifying-glass"/>
        </x-slot:search>
    @endcan
    <x-slot:actions>
        @can('permission.filter')
            <x-mary-button class="btn-soft btn-sm" :label="__('Filters')" @click="$wire.drawer=true" responsive
                           icon="o-funnel"/>
        @endcan
        @can('permission.view')
            <x-mary-button :link="route('permissions.create')" icon="o-plus" :label="__('Create')" class="btn-primary btn-sm"
                           responsive/>
        @endcan
    </x-slot:actions>

    <x-slot:content>
        <x-mary-table :headers="$headers" :rows="$permissions" :sort-by="$sortBy" with-pagination>
            @scope('cell_name', $permission)
            {{ str($permission->name)->replace('.', ' ')->headline() }}
            @endscope
            @scope('cell_permission', $permission)
            <x-mary-badge :value="$permission->name" class="badge-primary badge-soft " />
            @endscope
            @scope('actions', $permission)
            @can('permission.view')
                <x-mary-dropdown>
                    <x-slot:trigger>
                        <x-mary-button icon="o-ellipsis-horizontal" class="btn-circle"/>
                    </x-slot:trigger>

                    @can('permission.update')
                        <x-mary-menu-item :title="__('Edit')" icon="o-pencil"
                                          :link="route('permissions.edit', ['permission' => $permission->id])"/>
                    @endcan
                    @can('permission.delete')
                        @if ($permission->roles->isEmpty() && $permission->users->isEmpty())
                        <x-mary-menu-item :title="__('Delete')" icon="o-trash" class="text-error"
                                          @click="$dispatch('target-delete', { permission: {{ $permission->id }} })" spinner/>
                        @endif
                    @endcan
                </x-mary-dropdown>
            @endcan
            @endscope
        </x-mary-table>
    </x-slot:content>

    @can('permission.filter')
        <x-mary-drawer wire:model="drawer" :title="__('Filters')" right separator with-close-button class="lg:w-1/3">
            <x-slot:actions>
                <x-mary-button :label="__('Reset')" icon="o-x-mark" wire:click="clear" spinner class="btn-soft"/>
                <x-mary-button :label="__('Done')" icon="o-check" class="btn-primary" @click="$wire.drawer = false"/>
            </x-slot:actions>
        </x-mary-drawer>
    @endcan

    <x-mary-modal wire:model="modal" :title="__('Delete')" :subtitle="__('Are you sure?')" class="backdrop-blur">
        <x-slot:actions>
            <x-mary-button :label="__('Yes')" class="btn-error" wire:click="delete($wire.targetDelete)"
                           spinner="delete"/>
            <x-mary-button :label="__('Cancel')" class="btn-soft" @click="$wire.modal = false"/>
        </x-slot:actions>
    </x-mary-modal>
</x-pages.layout>

@script
<script>
    $wire.on('target-delete', (event) => {
        $wire.modal = true;
        $wire.targetDelete = event.permission;
    });
</script>
@endscript
