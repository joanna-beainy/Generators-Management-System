<?php

namespace App\Policies;

use App\Models\FuelPurchase;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FuelPurchasePolicy
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
    public function view(User $user, FuelPurchase $fuelPurchase): bool
    {
        return $user->id === $fuelPurchase->user_id;
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
    public function delete(User $user, FuelPurchase $fuelPurchase): bool
    {
        // User must own the purchase AND no liters should be consumed
        return $user->id === $fuelPurchase->user_id && 
               $fuelPurchase->remaining_liters == $fuelPurchase->liters_purchased;
    }
}