<?php

namespace Lernkarten\Policies;

use Course;
use Lernkarten\Models\Folder;
use Range;
use User;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class FolderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // The folders will be filtered by this user.
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Folder $folder): bool
    {
        switch ($folder->context_type) {
            case User::class:
                return $user->id === $folder->context_id;
            case Course::class:
                return $folder->getContext()->isAccessibleToUser($user);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param Course|User $context
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function create(User $user, Range $context): bool
    {
        switch (get_class($context)) {
            case User::class:
                return $user->id === $context->id;
            case Course::class:
                return $GLOBALS['perm']->have_studip_perm('tutor', $context->id, $user->id);
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Folder $folder): bool
    {
        switch ($folder->context_type) {
            case User::class:
                return $user->id === $folder->context_id;
            case Course::class:
                return $folder->getContext()->isEditableByUser($user);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Folder $folder): bool
    {
        return $this->update($user, $folder);
    }
}
