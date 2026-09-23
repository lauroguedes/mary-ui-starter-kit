<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use LauroGuedes\DemoMode\Facades\Demo;
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

        // On a demo, skip super-admin to prevent permission/role modifications
        $roleNames = Demo::enabled()
            ? ['admin', 'user-manager', 'user']
            : ['super-admin', 'admin', 'user-manager', 'user'];

        $roles = Role::whereIn('name', $roleNames)->pluck('name');

        $roles->each(function (string $role): void {
            $attributes = [
                'name' => str($role)->replace('-', ' ')->ucfirst(),
                'email' => $role . '@user.com',
                'status' => UserStatus::ACTIVE,
            ];

            /*
             * The admin is the account the demo publishes, so it takes the
             * password the reset staged a moment ago rather than the shared one.
             * Null outside a reset — running db:seed on its own leaves it with
             * the factory's password, which is what a local checkout wants.
             */
            $published = Demo::passwordFor($role . '@user.com');

            if (is_string($published)) {
                $attributes['password'] = Hash::make($published);
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
