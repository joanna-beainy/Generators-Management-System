<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientPolicy
{
    public function viewAny(User $user)
    {
        return true; 
    }

    public function view(User $user, Client $client)
    {
        return $user->id === $client->user_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(User $user, Client $client)
    {
        return $user->id === $client->user_id;
    }
}

