<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolesAndPermissionsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles if they don't exist
        $roles = [
            'super-admin',
            'admin',
            'user-manager',
            'permission-manager',
            'role-manager',
            'user',
        ];

        foreach ($roles as $roleName) {
            Role::create(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Define permissions by role
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

        // Function to insert permissions and return their IDs
        $insertPermissions = fn ($role) => collect($permissionsByRole[$role])
            ->map(fn ($name) => DB::table('permissions')->insertGetId(['name' => $name, 'guard_name' => 'web']))
            ->toArray();

        // Insert permissions for each role and get their IDs
        $permissionIdsByRole = [
            'user-manager' => $insertPermissions('user-manager'),
            'permission-manager' => $insertPermissions('permission-manager'),
            'role-manager' => $insertPermissions('role-manager'),
            'user' => $insertPermissions('user'),
        ];

        // Assign permissions to roles
        foreach ($permissionIdsByRole as $roleName => $permissionIds) {
            $role = Role::whereName($roleName)->first();

            DB::table('role_has_permissions')
                ->insert(
                    collect($permissionIds)->map(fn ($id) => [
                        'role_id' => $role->id,
                        'permission_id' => $id,
                    ])->toArray()
                );
        }

        $adminRole = Role::whereName('admin')->first();
        $adminRole->givePermissionTo(Permission::all());
        $adminRole->revokePermissionTo([
            'permission.create',
            'permission.update',
            'permission.delete',
        ]);

        Role::whereName('role-manager')->first()->givePermissionTo([
            'permission.view', 'permission.list',
        ]);

        // Update cache to know about the newly created permissions (required if using WithoutModelEvents in seeders)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
