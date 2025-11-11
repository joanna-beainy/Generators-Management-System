<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Auth\Access\Response;

class PaymentPolicy
{

    /**
     * Determine if the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view payments for their own client
    }
    
    /**
     * Determine if the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        return $payment->client->user_id === $user->id;
    }

    /**
     * Determine if the user can create payments.
     */
    public function create(User $user, int $clientId = null): bool
    {
        // Check if user owns the client and client is not offered
        if ($clientId) {
            return Client::where('id', $clientId)
                ->where('user_id', $user->id)
                ->where('is_offered', false) // Can't create payments for offered clients
                ->exists();
        }
        
        return true; // General create permission
    }

    /**
     * Determine if the user can delete the payment.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return $payment->client->user_id === $user->id;
    }
}