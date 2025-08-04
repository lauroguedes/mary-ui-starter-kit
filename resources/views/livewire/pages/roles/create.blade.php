<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new class extends Component {
    use Toast;
    use WithPagination;

    #[Validate('required|max:100|unique:roles')]
    public string $name = '';

    #[Validate('array')]
    public array $permissionsGiven = [];

    public string $search = '';

    public function save(): void
    {
        $this->authorize('role.create');

        $data = $this->validate();

        $role = Role::create([
            'name' => $data['name']
        ]);

        $role->givePermissionTo($data['permissionsGiven']);

        $this->success(__("Role {$role->name} created with success."), redirectTo: route('roles.index'));
    }

    public function permissions(): LengthAwarePaginator
    {
        return Permission::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->paginate(10);
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'permission', 'label' => 'Permission']
        ];
    }

    public function exception(Throwable $e, $stopPropagation): void
    {
        if ($e instanceof AuthorizationException) {
            $this->error($e->getMessage());

            $stopPropagation();
        }
    }

    public function with(): array
    {
        return [
            'permissions' => $this->permissions(),
            'headers' => $this->headers(),
        ];
    }

}; ?>

<x-pages.layout :page-title="__('Create Role')">
    <x-slot:content>
        <div class="grid gap-5 lg:grid-cols-2">
            <x-mary-form wire:submit="save">
                <x-mary-input :label="__('Name')" wire:model="name"/>

                <x-slot:actions>
                    <x-mary-button :label="__('Cancel')" :link="route('roles.index')" class="btn-soft"/>
                    <x-mary-button :label="__('Save')" icon="o-paper-airplane" spinner="save" type="submit"
                                   class="btn-primary"/>
                </x-slot:actions>
            </x-mary-form>
            <div class="hidden lg:block place-self-center w-full">
                <div class="m-3">
                    <x-partials.header-title :separator="true" :heading="__('Permissions')" />
                    @can('permission.search')
                    <x-mary-input class="input-sm" :placeholder="__('Search...')" wire:model.live.debounce="search" clearable
                                  icon="o-magnifying-glass"/>
                    @endcan
                </div>
                @can('permission.assign')
                <x-mary-table
                    :headers="$headers"
                    :rows="$permissions"
                    wire:model="permissionsGiven"
                    selectable
                    with-pagination>
                    @scope('cell_name', $permission)
                    {{ str($permission->name)->replace('.', ' ')->headline() }}
                    @endscope
                    @scope('cell_permission', $permission)
                    <x-mary-badge :value="$permission->name" class="badge-primary badge-soft " />
                    @endscope
                </x-mary-table>
                @endcan
            </div>
        </div>
    </x-slot:content>
</x-pages.layout>
