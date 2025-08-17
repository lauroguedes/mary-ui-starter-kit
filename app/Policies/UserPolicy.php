<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('user.list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if (
            $model->hasRole('super-admin')
            && ! $user->hasRole('super-admin')
        ) {
            return false;
        }

        if (
            $model->hasRole('admin')
            && ! $user->hasRole('admin')
        ) {
            return false;
        }

        return $user->can('user.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('user.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return $user->can('profile.update');
        }

        if (
            $model->hasRole('super-admin')
            && ! $user->hasRole('super-admin')
        ) {
            return false;
        }

        return $user->can('user.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return false;
        }

        if (
            $model->hasAnyRole('super-admin', 'admin')
            && ! $user->hasRole('super-admin')
        ) {
            return false;
        }

        return $user->can('user.delete');
    }

    public function assignRole(User $user, User $model): bool
    {
        if (
            $user->id === $model->id
            || $model->hasAnyRole('super-admin', 'admin')
        ) {
            return false;
        }

        return $user->can('role.assign');
    }

    public function manageStatus(User $user, User $model): bool
    {
        if (
            $user->id === $model->id
            || $model->hasAnyRole('super-admin', 'admin')
        ) {
            return false;
        }

        return $user->can('user.manage-status');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(): bool
    {
        return false;
    }
}
