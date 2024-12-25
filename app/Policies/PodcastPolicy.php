<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Podcast;
use Illuminate\Auth\Access\HandlesAuthorization;

class PodcastPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_podcast');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Podcast $podcast): bool
    {
        return $user->can('view_podcast');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_podcast');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Podcast $podcast): bool
    {
        return $user->can('update_podcast');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Podcast $podcast): bool
    {
        return $user->can('delete_podcast');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_podcast');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Podcast $podcast): bool
    {
        return $user->can('force_delete_podcast');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_podcast');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Podcast $podcast): bool
    {
        return $user->can('restore_podcast');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_podcast');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Podcast $podcast): bool
    {
        return $user->can('replicate_podcast');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_podcast');
    }
}
