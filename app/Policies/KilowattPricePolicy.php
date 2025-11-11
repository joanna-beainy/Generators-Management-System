<?php

namespace App\Policies;

use App\Models\KilowattPrice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KilowattPricePolicy
{
    /**
     * Determine if the user can view any kilowatt prices.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own kilowatt price
    }

    /**
     * Determine if the user can view the kilowatt price.
     */
    public function view(User $user, KilowattPrice $kilowattPrice): bool
    {
        return $kilowattPrice->user_id === $user->id;
    }

    /**
     * Determine if the user can create kilowatt prices.
     */
    public function create(User $user): bool
    {
        return true; // Users can create their own kilowatt price
    }

    /**
     * Determine if the user can update the kilowatt price.
     */
    public function update(User $user, KilowattPrice $kilowattPrice): bool
    {
        return $kilowattPrice->user_id === $user->id;
    }
}