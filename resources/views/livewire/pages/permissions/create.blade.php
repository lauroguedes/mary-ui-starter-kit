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

    #[Validate('required|max:100|regex:/^[a-z]+(\.[a-z]+)*$/|unique:permissions')]
    public string $name = '';

    #[Validate('array')]
    public array $rolesGiven = [];

    public string $search = '';

    public function save(): void
    {
        $this->authorize('permission.create');

        $data = $this->validate();

        $permission = Permission::create([
            'name' => $data['name']
        ]);

        $permission->assignRole($data['rolesGiven']);

        $this->success(__("Permission {$permission->name} created with success."), redirectTo: route('permissions.index'));
    }

    public function roles(): LengthAwarePaginator
    {
        return Role::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->paginate(10);
    }

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name'],
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
            'roles' => $this->roles(),
            'headers' => $this->headers(),
        ];
    }

}; ?>

<x-pages.layout :page-title="__('Create Permission')">
    <x-slot:content>
        <div class="grid gap-5 lg:grid-cols-2">
            <x-mary-form wire:submit="save">
                <x-mary-input :label="__('Name')" wire:model="name" :hint="__('Use lowercase and dot notation. Ex: model.action')"/>

                <x-slot:actions>
                    <x-mary-button :label="__('Cancel')" :link="route('permissions.index')" class="btn-soft"/>
                    <x-mary-button :label="__('Save')" icon="o-paper-airplane" spinner="save" type="submit"
                                   class="btn-primary"/>
                </x-slot:actions>
            </x-mary-form>
            <div class="hidden lg:block place-self-center w-full">
                <div class="m-3">
                    <x-partials.header-title :separator="true" :heading="__('Roles')" />
                    @can('role.search')
                        <x-mary-input class="input-sm" :placeholder="__('Search...')" wire:model.live.debounce="search" clearable
                                      icon="o-magnifying-glass"/>
                    @endcan
                </div>
                @can('role.assign')
                    <x-mary-table
                        :headers="$headers"
                        :rows="$roles"
                        wire:model="rolesGiven"
                        selectable
                        with-pagination />
                @endcan
            </div>
        </div>
    </x-slot:content>
</x-pages.layout>
