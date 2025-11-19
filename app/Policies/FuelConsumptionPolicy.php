<?php

namespace App\Policies;

use App\Models\FuelConsumption;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FuelConsumptionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FuelConsumption $fuelConsumption): bool
    {
        return $user->id === $fuelConsumption->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FuelConsumption $fuelConsumption): bool
    {
        return $user->id === $fuelConsumption->user_id;
    }
}