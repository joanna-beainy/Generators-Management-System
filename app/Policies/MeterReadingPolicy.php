<?php

namespace App\Policies;

use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MeterReadingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any meter readings.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view meter readings for their own clients
    }

    /**
     * Determine whether the user can view the meter reading.
     */
    public function view(User $user, MeterReading $meterReading): bool
    {
        return $meterReading->client->user_id === $user->id;
    }

    /**
     * Determine if the user can create meter readings.
     */
    public function create(User $user): bool
    {
        return true; // Users can create meter readings for their own clients
    }

    /**
     * Determine whether the user can update the meter reading.
     */
    public function update(User $user, MeterReading $meterReading): bool
    {
        // Only allow updating if the meter reading belongs to a client of this user
        return $meterReading->client->user_id === $user->id;
    }
}
