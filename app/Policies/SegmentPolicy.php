<?php

namespace App\Policies;

use App\Models\User;

class SegmentPolicy
{
    /**
     * Determine whether the user can view insights.
     */
    public function view(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can refresh segment data.
     */
    public function refresh(User $user): bool
    {
        return $user->role === 'admin';
    }
}
