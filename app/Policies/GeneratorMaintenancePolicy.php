<?php

namespace App\Policies;

use App\Models\GeneratorMaintenance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeneratorMaintenancePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GeneratorMaintenance $generatorMaintenance): bool
    {
        return $user->id === $generatorMaintenance->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, GeneratorMaintenance $generatorMaintenance): bool
    {
        return $user->id === $generatorMaintenance->user_id;
    }
}