<?php

namespace App\Policies;

use App\Models\AttendanceIn;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_attendance');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AttendanceIn $AttendanceIn): bool
    {
        // return $user->can('view_attendance',);
        return $user->id === $AttendanceIn->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_attendance');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AttendanceIn $AttendanceIn): bool
    {
        return $user->can('update_attendance');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AttendanceIn $AttendanceIn): bool
    {
        // return $user->can('delete_attendance');
        return $user->id === $AttendanceIn->user_id;
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_attendance');
    }
}
