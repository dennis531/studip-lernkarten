<?php

namespace Lernkarten\Policies;

use Lernkarten\Models\Instance;
use User;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class InstancePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAnyOfUsers(User $user, User $observed): bool
    {
        return $user->id === $observed->id;
    }
}
