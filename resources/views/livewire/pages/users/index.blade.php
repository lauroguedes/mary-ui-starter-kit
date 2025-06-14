<?php

use App\Models\User;
use App\Enums\UserStatus;
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

    public ?int $status = null;

    public bool $drawer = false;

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];

    public bool $modal = false;

    public mixed $targetDelete = null;

    public function headers(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'avatar', 'label' => 'Avatar', 'sortable' => false, 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'email', 'label' => 'Email', 'sortable' => false]
        ];
    }

    public function statusGroup(): array
    {
        return [
            ['id' => UserStatus::ACTIVE, 'name' => UserStatus::ACTIVE->label()],
            ['id' => UserStatus::INACTIVE, 'name' => UserStatus::INACTIVE->label()],
            ['id' => UserStatus::SUSPENDED, 'name' => UserStatus::SUSPENDED->label()],
        ];
    }

    public function delete(User $user): void
    {
        $user->delete();

        $this->modal = false;

        $this->success(
            __("User {$user->name} has been deleted."),
        );
    }

    public function edit(User $user): void
    {
        $this->redirectRoute('users.edit', ['user' => $user->id], false, true);
    }

    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->when($this->status, fn(Builder $q) => $q->where('status', $this->status))
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'users' => $this->users(),
            'headers' => $this->headers(),
            'statusGroup' => $this->statusGroup(),
        ];
    }
}; ?>

<x-pages.layout :page-title="__('Users')">
    <x-slot:search>
        <x-mary-input class="input-sm" :placeholder="__('Search...')" wire:model.live.debounce="search" clearable
            icon="o-magnifying-glass" />
    </x-slot:search>
    <x-slot:actions>
        <x-mary-button class="btn-soft btn-sm" :label="__('Filters')" @click="$wire.drawer=true" responsive
            icon="o-funnel" />
        <x-mary-button :link="route('users.create')" icon="o-plus" :label="__('Create')" class="btn-primary btn-sm"
            responsive />
    </x-slot:actions>

    <x-slot:content>
        <x-mary-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination>
            @scope('cell_avatar', $user)
            <div class="indicator tooltip" data-tip="{{ $user->status->label() }}">
                <span @class([
                    'indicator-item status',
                    'status-success' => $user->status === UserStatus::ACTIVE,
                    'status-warning' => $user->status === UserStatus::INACTIVE,
                    'status-error' => $user->status === UserStatus::SUSPENDED,
                ])></span>
                <x-mary-avatar image="{{ $user->avatar ?? '/images/empty-user.jpg' }}" class="!w-8 !rounded-lg" />
            </div>
            @endscope

            @scope('actions', $user)
            @if($user->id !== auth()->id())
                <x-mary-dropdown>
                    <x-slot:trigger>
                        <x-mary-button icon="o-ellipsis-horizontal" class="btn-circle" />
                    </x-slot:trigger>

                    <x-mary-menu-item :title="__('Edit')" icon="o-pencil" :link="route('users.edit', ['user' => $user->id])" />
                    <x-mary-menu-item :title="__('Delete')" icon="o-trash" class="text-error"
                        @click="$dispatch('target-delete', { user: {{ $user->id }} })" spinner />
                </x-mary-dropdown>
            @endif
            @endscope
        </x-mary-table>
    </x-slot:content>

    <x-mary-drawer wire:model="drawer" :title="__('Filters')" right separator with-close-button class="lg:w-1/3">
        <x-mary-group :label="__('Status')" wire:model.live="status" :options="$statusGroup"
            class="[&:checked]:!btn-primary" />

        <x-slot:actions>
            <x-mary-button :label="__('Reset')" icon="o-x-mark" wire:click="clear" spinner class="btn-soft" />
            <x-mary-button :label="__('Done')" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-mary-drawer>

    <x-mary-modal wire:model="modal" :title="__('Delete')" :subtitle="__('Are you sure?')" class="backdrop-blur">
        <x-slot:actions>
            <x-mary-button :label="__('Yes')" class="btn-error" wire:click="delete($wire.targetDelete)"
                spinner="delete" />
            <x-mary-button :label="__('Cancel')" class="btn-soft" @click="$wire.modal = false" />
        </x-slot:actions>
    </x-mary-modal>
</x-pages.layout>

@script
<script>
    $wire.on('target-delete', (event) => {
        $wire.modal = true;
        $wire.targetDelete = event.user;
    });
</script>
@endscript
