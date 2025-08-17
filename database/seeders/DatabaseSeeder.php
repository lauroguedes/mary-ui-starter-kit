<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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

        // Create one user for each role
        $roles = Role::whereIn('name', [
            'super-admin',
            'admin',
            'user-manager',
            'user',
        ])->pluck('name');

        $roles->each(function (string $role): void {
            $user = User::factory()->create([
                'name' => str($role)->replace('-', ' ')->ucfirst(),
                'email' => $role . '@user.com',
                'status' => UserStatus::ACTIVE,
            ]);

            $user->assignRole($role);
        });

        User::whereEmail('user-manager@user.com')->first()->assignRole('user');

        // Create 50 default users with only the "user" role
        User::factory(50)->create()->each(function ($user): void {
            $user->assignRole('user');
        });
    }
}
