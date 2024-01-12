<?php

namespace Lernkarten\Policies;

use Lernkarten\Models\Card;
use Lernkarten\Models\Deck;
use User;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // The cards will be filtered by this user.
        return true;
    }

    /**
     * Determine whether the user can view any models.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function viewAnyOfDeck(User $user, Deck $observed): bool
    {
        return $observed->owner_id === $user->id || $observed->isSharedWith($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Card $card): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Card $card): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Card $card): bool
    {
        return true;
    }
}
