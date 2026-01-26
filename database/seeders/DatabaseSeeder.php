<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call(RolesAndPermissionsSeeder::class);

        // In demo mode, skip super-admin to prevent permission/role modifications
        $roleNames = config('app.demo.enabled')
            ? ['admin', 'user-manager', 'user']
            : ['super-admin', 'admin', 'user-manager', 'user'];

        $roles = Role::whereIn('name', $roleNames)->pluck('name');

        $roles->each(function (string $role): void {
            $attributes = [
                'name' => str($role)->replace('-', ' ')->ucfirst(),
                'email' => $role . '@user.com',
                'status' => UserStatus::ACTIVE,
            ];

            if ($role === 'admin' && config('app.demo.enabled')) {
                $attributes['password'] = Hash::make(cache('demo-password', 'secret'));
            }

            $user = User::factory()->create($attributes);

            $user->assignRole($role);
        });

        User::whereEmail('user-manager@user.com')->first()->assignRole('user');

        // Create 50 default users with only the "user" role
        User::factory(50)->create()->each(function ($user): void {
            $user->assignRole('user');
        });
    }
}
