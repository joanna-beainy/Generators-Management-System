<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;
use App\Models\Maintenance;
use Illuminate\Auth\Access\Response;

class MaintenancePolicy
{

    /**
     * Determine if the user can view the maintenance record.
     */
    public function view(User $user, Maintenance $maintenance): bool
    {
        return $maintenance->client->user_id === $user->id;
    }

    /**
     * Determine if the user can create maintenance records.
     */
    public function create(User $user, int $clientId = null): bool
    {
        // Check if user owns the client
        if ($clientId) {
            return Client::where('id', $clientId)
                ->where('user_id', $user->id)
                ->where('is_offered', false) // Can't create maintenance for offered clients
                ->exists();
        }
        
        return true; // General create permission
    }

    /**
     * Determine if the user can delete the maintenance record.
     */
    public function delete(User $user, Maintenance $maintenance): bool
    {
        return $maintenance->client->user_id === $user->id;
    }

    
}