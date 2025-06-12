<?php

use App\Models\User;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Traits\ClearsFilters;

new class extends Component {
    use Toast;
    use WithPagination;
    use ClearsFilters;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email', 'sortable' => false]
        ];
    }

    public function delete(User $user): void
    {
        $user->delete();
        $this->success(
            __("User {$user->name} has been deleted."),
        );
    }

    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers()
        ];
    }
}; ?>

<x-pages.layout :title="__('Users')">
    <x-slot:search>
        <x-mary-input class="input-sm" :placeholder="__('Search...')" wire:model.live.debounce="search" clearable
            icon="o-magnifying-glass" />
    </x-slot:search>
    <x-slot:actions>
        <x-mary-button class="btn-soft btn-sm" :label="__('Filters')" @click="$wire.drawer=true" responsive
            icon="o-funnel" />
        <x-mary-button icon="o-plus" :label="__('Create')" class="btn-primary btn-sm" responsive />
    </x-slot:actions>

    <x-mary-card shadow>
        <x-mary-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination>
            @scope('actions', $user)
            <x-mary-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner
                class="btn-ghost btn-sm text-error" />
            @endscope
        </x-mary-table>
    </x-mary-card>

    <x-mary-drawer wire:model="drawer" :title="__('Filters')" right separator with-close-button class="lg:w-1/3">
        <x-mary-input :placeholder="__('Search...')" wire:model.live.debounce="search" icon="o-magnifying-glass"
            @keydown.enter="$wire.drawer = false" />

        <x-slot:actions>
            <x-mary-button :label="__('Reset')" icon="o-x-mark" wire:click="clear" spinner />
            <x-mary-button :label="__('Done')" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-mary-drawer>
</x-pages.layout>
