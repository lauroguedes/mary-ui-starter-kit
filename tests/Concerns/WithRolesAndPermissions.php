<?php

declare(strict_types=1);

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait WithRolesAndPermissions
{
    protected static array $cachedRoles = [];

    protected static array $cachedPermissions = [];

    protected function setUpRolesAndPermissions(): void
    {
        if (Role::count() === 0) {
            $this->seedRolesAndPermissions();
        } else {
            $this->loadCachedRolesAndPermissions();
        }
    }

    protected function loadCachedRolesAndPermissions(): void
    {
        if (empty(static::$cachedRoles)) {
            static::$cachedRoles = Role::all()->keyBy('name')->toArray();
        }

        if (empty(static::$cachedPermissions)) {
            static::$cachedPermissions = Permission::all()->keyBy('name')->toArray();
        }
    }

    protected function seedRolesAndPermissions(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            'super-admin',
            'admin',
            'user-manager',
            'permission-manager',
            'role-manager',
            'user',
        ];

        foreach ($roles as $roleName) {
            static::$cachedRoles[$roleName] = Role::create(['name' => $roleName, 'guard_name' => 'web']);
        }

        $permissionsByRole = [
            'user-manager' => [
                'user.create', 'user.update', 'user.delete', 'user.view', 'user.list',
                'user.search', 'user.filter', 'user.sort', 'user.manage-status', 'user.manage-avatar',
            ],
            'permission-manager' => [
                'permission.view', 'permission.list', 'permission.create', 'permission.update', 'permission.delete',
                'permission.assign', 'permission.revoke', 'permission.search', 'permission.filter', 'permission.sort',
            ],
            'role-manager' => [
                'role.view', 'role.list', 'role.create', 'role.update', 'role.delete', 'role.assign', 'role.revoke',
                'role.search', 'role.filter', 'role.sort',
            ],
            'user' => [
                'user.login',
                'dashboard.view',
                'profile.settings', 'profile.view', 'profile.password', 'profile.update', 'profile.delete',
            ],
        ];

        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(function ($name) {
                $permission = Permission::create(['name' => $name, 'guard_name' => 'web']);
                static::$cachedPermissions[$name] = $permission;

                return $permission->id;
            })
            ->toArray();

        $permissionIdsByRole = [
            'user-manager' => $insertPermissions('user-manager'),
            'permission-manager' => $insertPermissions('permission-manager'),
            'role-manager' => $insertPermissions('role-manager'),
            'user' => $insertPermissions('user'),
        ];

        foreach ($permissionIdsByRole as $roleName => $permissionIds) {
            $role = static::$cachedRoles[$roleName];

            DB::table('role_has_permissions')
                ->insert(
                    collect($permissionIds)->map(fn ($id): array => [
                        'role_id' => $role->id,
                        'permission_id' => $id,
                    ])->toArray()
                );
        }

        $adminRole = static::$cachedRoles['admin'];
        $adminRole->givePermissionTo(Permission::all());
        $adminRole->revokePermissionTo([
            'permission.create',
            'permission.update',
            'permission.delete',
        ]);

        static::$cachedRoles['role-manager']->givePermissionTo([
            'permission.view', 'permission.list',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    protected function getRole(string $roleName): Role
    {
        return static::$cachedRoles[$roleName] ?? Role::whereName($roleName)->first();
    }

    protected function getPermission(string $permissionName): Permission
    {
        return static::$cachedPermissions[$permissionName] ?? Permission::whereName($permissionName)->first();
    }
}
