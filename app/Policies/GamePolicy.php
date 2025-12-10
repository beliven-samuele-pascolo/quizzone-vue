<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class GamePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function manage(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function buzz(User $user): bool
    {
        return $user->role === UserRole::Player && ! $user->banned;
    }
}
