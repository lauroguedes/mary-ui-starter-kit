<?php

use App\Traits\ClearsFilters;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

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
        ];
    }

    public function delete(Role $role): void
    {
        $this->authorize('role.delete');

        throw_if(
            $role->users->isNotEmpty(),
            AuthorizationException::class,
            __('Before deleting the role, remove the user binding.')
        );

        $role->delete();

        $this->modal = false;

        $this->success(
            __("Role {$role->name} has been deleted."),
        );
    }

    public function roles(): LengthAwarePaginator
    {
        return Role::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->with(['permissions', 'users'])
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'roles' => $this->roles(),
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

<x-pages.layout :page-title="__('Roles')">
    @can('role.search')
        <x-slot:search>
            <x-mary-input class="input-sm" :placeholder="__('Search...')" wire:model.live.debounce="search" clearable
                          icon="o-magnifying-glass"/>
        </x-slot:search>
    @endcan
    <x-slot:actions>
        @can('role.filter')
            <x-mary-button class="btn-soft btn-sm" :label="__('Filters')" @click="$wire.drawer=true" responsive
                           icon="o-funnel"/>
        @endcan
        @can('role.create')
            <x-mary-button :link="route('roles.create')" icon="o-plus" :label="__('Create')" class="btn-primary btn-sm"
                           responsive/>
        @endcan
    </x-slot:actions>

    <x-slot:content>
        <x-mary-table :headers="$headers" :rows="$roles" :sort-by="$sortBy" with-pagination>
            @scope('actions', $role)
            <div class="inline-flex gap-2 items-center justify-end">
                @if ($role->permissions->isNotEmpty())
                    <x-mary-popover>
                        <x-slot:trigger>
                            <x-mary-button icon="fas.user-shield" class="btn-circle btn-ghost" />
                        </x-slot:trigger>
                        <x-slot:content class="border border-warning max-w-80">
                            <div class="flex flex-wrap gap-1">
                                @foreach($role->permissions as $permission)
                                    <x-mary-badge :value="$permission->name" class="badge-primary badge-xs" />
                                @endforeach
                            </div>
                        </x-slot:content>
                    </x-mary-popover>
                @endif
                @canany(['role.update', 'role.delete'])
                    <x-mary-dropdown>
                        <x-slot:trigger>
                            <x-mary-button icon="o-ellipsis-horizontal" class="btn-circle"/>
                        </x-slot:trigger>

                        @can('role.update')
                            <x-mary-menu-item :title="__('Edit')" icon="o-pencil"
                                              :link="route('roles.edit', ['role' => $role->id])"/>
                        @endcan
                        @can('role.delete')
                            @if ($role->users->isEmpty())
                                <x-mary-menu-item :title="__('Delete')" icon="o-trash" class="text-error"
                                                  @click="$dispatch('target-delete', { role: {{ $role->id }} })" spinner/>
                            @endif
                        @endcan
                    </x-mary-dropdown>
                @endcanany
            </div>
            @endscope
        </x-mary-table>
    </x-slot:content>

    @can('role.filter')
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
        $wire.targetDelete = event.role;
    });
</script>
@endscript
