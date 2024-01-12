<?php

namespace Lernkarten\Policies;

use Course;
use Lernkarten\Models\SharedDeck;
use User;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class SharedDeckPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // The shared decks will be filtered by this user.
        return true;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function viewAnyOfCourse(User $user, Course $observed): bool
    {
        return $GLOBALS['perm']->have_studip_perm('autor', $observed->id, $user->id);
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAnyOfUser(User $user, User $observed): bool
    {
        return $user->id === $observed->id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SharedDeck $sharedDeck): bool
    {
        return $sharedDeck->sharer_id === $user->id || $sharedDeck->isSharedWith($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Anyone may share a deck.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SharedDeck $sharedDeck): bool
    {
        return $sharedDeck->sharer_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SharedDeck $sharedDeck): bool
    {
        return $this->update($user, $sharedDeck);
    }

    /**
     * Determine whether the user can colearn the model.
     */
    public function colearn(User $user, SharedDeck $sharedDeck): bool
    {
        return $sharedDeck->isSharedWith($user);
    }

    /**
     * Determine whether the user can copy the model.
     */
    public function copy(User $user, SharedDeck $sharedDeck): bool
    {
        return $this->colearn($user, $sharedDeck);
    }
}
