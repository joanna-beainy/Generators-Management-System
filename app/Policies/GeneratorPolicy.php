<?php

namespace App\Policies;

use App\Models\Generator;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GeneratorPolicy
{

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Generator $generator): bool
    {
        return $user->id === $generator->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Generator $generator): bool
    {
        return $generator->user_id === $user->id && $generator->clients()->count() === 0;
    }

    public function restore(User $user, Generator $generator): bool
    {
        return $user->is_admin === true;
    }

    public function forceDelete(User $user, Generator $generator): bool
    {
        return $user->is_admin === true;
    }

}
