<?php

namespace App\Policies;

use App\Models\ExchangeRate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExchangeRatePolicy
{
    /**
     * Determine if the user can view any exchange rates.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own exchange rate
    }

    /**
     * Determine if the user can view the exchange rate.
     */
    public function view(User $user, ExchangeRate $exchangeRate): bool
    {
        return $exchangeRate->user_id === $user->id;
    }

    /**
     * Determine if the user can create exchange rates.
     */
    public function create(User $user): bool
    {
        return true; // Users can create their own exchange rate
    }

    /**
     * Determine if the user can update the exchange rate.
     */
    public function update(User $user, ExchangeRate $exchangeRate): bool
    {
        return $exchangeRate->user_id === $user->id;
    }
}