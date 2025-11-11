<?php

namespace App\Policies;

use App\Models\Generator;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GeneratorPolicy
{
    /**
     * Determine if the user can view any generators.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own generators
    }

    /**
     * Determine if the user can view the generator.
     */
    public function view(User $user, Generator $generator): bool
    {
        return $generator->user_id === $user->id;
    }

    /**
     * Determine if the user can create generators.
     */
    public function create(User $user): bool
    {
        return true; // Users can create generators for themselves
    }

    /**
     * Determine if the user can delete the generator.
     */
    public function delete(User $user, Generator $generator): bool
    {
        // Check if generator is being used by any clients
        if ($generator->clients()->exists()) {
            return false; // Cannot delete if generator has clients
        }
        
        return $generator->user_id === $user->id;
    }

}