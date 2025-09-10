<?php

namespace App\Policies;

use App\Models\MeterCategory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeterCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MeterCategory $meterCategory): bool
    {
        return $meterCategory->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MeterCategory $meterCategory): bool
    {
        return $meterCategory->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MeterCategory $meterCategory): bool
    {
        return $meterCategory->user_id === $user->id && $meterCategory->customers()->count() === 0;
    }

    public function restore(User $user, MeterCategory $meterCategory): bool
    {
        return $user->is_admin === true;
    }

    public function forceDelete(User $user, MeterCategory $meterCategory): bool
    {
        return $user->is_admin === true;
    }

}
