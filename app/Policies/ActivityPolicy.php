<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityPolicy
{
    /**
     * Determine whether the user can view any activity logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view activity log');
    }

    /**
     * Determine whether the user can view a specific activity log.
     */
    public function view(User $user, Activity $activity): bool
    {
        return $user->can('view activity log');
    }

    /**
     * Determine whether the user can delete an activity log.
     */
    public function delete(User $user, Activity $activity): bool
    {
        return $user->can('delete activity log');
    }

    /**
     * Determine whether the user can restore an activity log.
     */
    public function restore(User $user, Activity $activity): bool
    {
        return $user->can('restore activity log');
    }

    /**
     * Determine whether the user can force delete an activity log.
     */
    public function forceDelete(User $user, Activity $activity): bool
    {
        return $user->can('force delete activity log');
    }
}
