<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use App\Enums\UserStatus;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new class extends Component {
    use Toast, WithFileUploads, WithPagination;

    public User $user;

    #[Validate('required|max:255')]
    public string $name = '';

    public string $email = '';

    #[Validate('nullable')]
    public mixed $avatar = null;

    #[Validate('required|int')]
    public int $status;

    #[Validate('array')]
    public array $rolesGiven = [];

    #[Validate('array')]
    public array $permissionsGiven = [];

    public string $searchRole = '';

    public string $searchPermission = '';

    public array $statusOptions;

    public function mount(): void
    {
        if (auth()->id() === $this->user->id) {
            $this->redirectRoute('settings.profile');
        }

        $this->fill($this->user);

        $this->rolesGiven = $this->user
            ->roles()
            ->pluck('id')
            ->toArray();

        $this->permissionsGiven = $this->user
            ->permissions()
            ->pluck('id')
            ->toArray();

        $this->statusOptions = UserStatus::all();
    }

    protected function rules(): array
    {
        return [
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user->id)
            ]
        ];
    }

    public function save(): void
    {
        $this->authorize('update', $this->user);

        $validated = $this->validate();

        $this->processUpload($validated);

        $this->user->update(Arr::except($validated, ['rolesGiven', 'permissionsGiven']));

        if (auth()->user()->can('role.assign')) {
            $this->user->syncRoles($this->rolesGiven);
        }

        if (auth()->user()->can('permission.assign')) {
            $this->user->syncPermissions($this->permissionsGiven);
        }

        $this->success(__('User updated with success.'), redirectTo: route('users.index'));
    }

    private function processUpload(array &$validated): void
    {
        if (!$this->avatar || !($this->avatar instanceof \Illuminate\Http\UploadedFile)) {
            return;
        }

        $this->validate([
            'avatar' => 'image|max:1024'
        ]);

        if ($this->user->avatar) {
            $path = str($this->user->avatar)->after('/storage/');
            \Storage::disk('public')->delete($path);
        }

        $url = $this->avatar->store('users', 'public');
        $validated['avatar'] = "/storage/{$url}";
    }

    #[Computed]
    public function rowDecoration(): array
    {
        return [
            'bg-warning/20' => fn(Role $role) => $role->name === 'super-admin',
        ];
    }

    public function roles(): LengthAwarePaginator
    {
        return Role::query()
            ->when($this->searchRole, fn(Builder $q) => $q->where('name', 'like', "%$this->searchRole%"))
            ->when(!auth()->user()->hasRole('super-admin'), fn(Builder $q) => $q->where('name', '!=', 'super-admin')
            )
            ->paginate(10);
    }

    public function permissions(): LengthAwarePaginator
    {
        return Permission::query()
            ->when($this->searchPermission, fn(Builder $q) => $q->where('name', 'like', "%$this->searchPermission%"))
            ->paginate(10);
    }

    public function headersRole(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name'],
        ];
    }

    public function headersPermission(): array
    {
        return [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name'],
        ];
    }

    public function with(): array
    {
        $data = [
            'roles' => $this->roles(),
            'headersRole' => $this->headersRole(),
        ];

        if (auth()->user()->hasRole('super-admin')) {
            $data = array_merge($data, [
                'permissions' => $this->permissions(),
                'headersPermission' => $this->headersPermission(),
            ]);
        }

        return $data;
    }

    public function exception(Throwable $e, $stopPropagation): void
    {
        if ($e instanceof AuthorizationException) {
            $this->error($e->getMessage());

            $stopPropagation();
        }
    }

}; ?>

<x-pages.layout :page-title="__('Update') . ' - ' . $user->name">
    <x-slot:content>
        <div class="grid gap-5 lg:grid-cols-2">
            <x-mary-form wire:submit="save">
                @can('user.manage-avatar')
                    <div class="indicator">
                    <span @class([
                        'indicator-item status',
                        'status-success' => $user->status === UserStatus::ACTIVE,
                        'status-warning' => $user->status === UserStatus::INACTIVE,
                        'status-error' => $user->status === UserStatus::SUSPENDED,
                    ])></span>
                        <x-mary-file wire:model="avatar" accept="image/png, image/jpeg" crop-after-change>
                            <img src="{{ $user->avatar ?? '/images/empty-user.jpg' }}" class="h-36 rounded-lg"/>
                        </x-mary-file>
                    </div>
                @endcan

                <x-mary-input :label="__('Name')" wire:model="name"/>
                <x-mary-input :disabled="auth()->user()->cannot('manageStatus', $user)" :label="__('Email')" wire:model="email"/>
                @can('user.manage-status')
                    <x-mary-group :disabled="auth()->user()->cannot('manageStatus', $user)" :label="__('Status')" wire:model="status" :options="$statusOptions"
                                  class="[&:checked]:!btn-primary"/>
                @endcan

                <x-slot:actions>
                    <x-mary-button :label="__('Cancel')" :link="route('users.index')" class="btn-soft"/>
                    <x-mary-button :label="__('Save')" icon="o-paper-airplane" spinner="save" type="submit"
                                   class="btn-primary"/>
                </x-slot:actions>
            </x-mary-form>
            <div class="hidden lg:block place-self-center w-full">
                @unlessrole('super-admin')
                @can('assignRole', $user)
                    <div class="m-3">
                        <x-partials.header-title :separator="true" :heading="__('Roles')"/>
                        @can('role.search')
                            <x-mary-input class="input-sm" :placeholder="__('Search...')"
                                          wire:model.live.debounce="searchRole" clearable
                                          icon="o-magnifying-glass"/>
                        @endcan
                    </div>
                    <x-mary-table
                        :headers="$headersRole"
                        :rows="$roles"
                        :row-decoration="$this->rowDecoration"
                        wire:model="rolesGiven"
                        selectable
                        with-pagination/>
                @else
                    <img src="/images/user-action-page.svg" width="300" class="mx-auto"/>
                @endcan
                @else
                    <img src="/images/user-action-page.svg" width="300" class="mx-auto"/>
                    @endunlessrole
            </div>
        </div>
        @role('super-admin')
        <div class="flex gap-5 w-full">
            <div class="w-full lg:w-1/2">
                <div class="m-3">
                    <x-partials.header-title :separator="true" :heading="__('Roles')"/>
                    <x-mary-input class="input-sm" :placeholder="__('Search...')"
                                  wire:model.live.debounce="searchRole" clearable
                                  icon="o-magnifying-glass"/>
                </div>
                <x-mary-table
                    :headers="$headersRole"
                    :rows="$roles"
                    :row-decoration="$this->rowDecoration"
                    wire:model="rolesGiven"
                    selectable
                    with-pagination/>
            </div>
            <div class="w-full lg:w-1/2">
                <div class="m-3">
                    <x-partials.header-title :separator="true" :heading="__('Permissions')"/>
                    <x-mary-input class="input-sm" :placeholder="__('Search...')"
                                  wire:model.live.debounce="searchPermission" clearable
                                  icon="o-magnifying-glass"/>
                </div>
                <x-mary-table
                    :headers="$headersPermission"
                    :rows="$permissions"
                    wire:model="permissionsGiven"
                    selectable
                    with-pagination/>
            </div>
        </div>
        @endrole
    </x-slot:content>
</x-pages.layout>

@push('scripts')
    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css"/>
@endpush
