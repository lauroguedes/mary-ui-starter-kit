<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\Permission\Models\Permission;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Spatie\Permission\Models\Permission>
 */

final class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $actions = ['create', 'read', 'update', 'delete', 'list', 'view', 'manage'];
        $resources = ['user', 'post', 'comment', 'product', 'order', 'category', 'report'];
        
        return [
            'name' => fake()->randomElement($resources) . '.' . fake()->randomElement($actions),
            'guard_name' => 'web',
        ];
    }
}