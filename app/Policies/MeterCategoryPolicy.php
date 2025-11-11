<?php

namespace App\Policies;

use App\Models\MeterCategory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeterCategoryPolicy
{
    /**
     * Determine if the user can view any meter categories.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own meter categories
    }

    /**
     * Determine if the user can view the meter category.
     */
    public function view(User $user, MeterCategory $meterCategory): bool
    {
        return $meterCategory->user_id === $user->id;
    }

    /**
     * Determine if the user can create meter categories.
     */
    public function create(User $user): bool
    {
        return true; // Users can create meter categories for themselves
    }

    /**
     * Determine if the user can update the meter category.
     */
    public function update(User $user, MeterCategory $meterCategory): bool
    {
        return $meterCategory->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the meter category.
     */
    public function delete(User $user, MeterCategory $meterCategory): bool
    {
        // Check if category is being used by any clients
        if ($meterCategory->clients()->exists()) {
            return false; // Cannot delete if category has clients
        }
        
        return $meterCategory->user_id === $user->id;
    }

}