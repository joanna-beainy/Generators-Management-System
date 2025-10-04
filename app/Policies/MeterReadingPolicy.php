<?php

namespace App\Policies;

use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MeterReadingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the meter reading.
     */
    public function view(User $user, MeterReading $meterReading): bool
    {
        return $meterReading->client->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the meter reading.
     */
    public function update(User $user, MeterReading $meterReading): bool
    {
        // Only allow updating if the meter reading belongs to a client of this user
        return $meterReading->client->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the meter reading.
     */
    public function delete(User $user, MeterReading $meterReading): bool
    {
        return $meterReading->client->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the meter reading.
     */
    public function restore(User $user, MeterReading $meterReading): bool
    {
        return $meterReading->client->user_id === $user->id;
    }

    /**
     * Determine whether the user can permanently delete the meter reading.
     */
    public function forceDelete(User $user, MeterReading $meterReading): bool
    {
        return $meterReading->client->user_id === $user->id;
    }
}
