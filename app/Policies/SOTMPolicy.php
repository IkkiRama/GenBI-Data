<?php

namespace App\Policies;

use App\Models\User;
use App\Models\SOTM;
use Illuminate\Auth\Access\HandlesAuthorization;

class SOTMPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_s::o::t::m');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SOTM $sOTM): bool
    {
        return $user->can('view_s::o::t::m');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_s::o::t::m');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SOTM $sOTM): bool
    {
        return $user->can('update_s::o::t::m');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SOTM $sOTM): bool
    {
        return $user->can('delete_s::o::t::m');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_s::o::t::m');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, SOTM $sOTM): bool
    {
        return $user->can('force_delete_s::o::t::m');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_s::o::t::m');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, SOTM $sOTM): bool
    {
        return $user->can('restore_s::o::t::m');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_s::o::t::m');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, SOTM $sOTM): bool
    {
        return $user->can('replicate_s::o::t::m');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_s::o::t::m');
    }
}
