<?php

namespace App\Policies;

use App\Models\User;

class rolePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function accessRoles(User $user)
    {
        return $user->hasAnyRoles(['super_admin']);
    }

    public function manageRoles(User $user)
    {
        return $user->hasAnyRoles(['super_admin']);
    }
}
