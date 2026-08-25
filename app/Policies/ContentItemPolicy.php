<?php

namespace App\Policies;

use App\Models\Content\ContentItem;
use App\Models\User;

class ContentItemPolicy
{
    /**
     * Determine whether the user can view any content items.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the content item.
     */
    public function view(User $user, ContentItem $contentItem): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can create content items.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the content item.
     */
    public function update(User $user, ContentItem $contentItem): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the content item.
     */
    public function delete(User $user, ContentItem $contentItem): bool
    {
        return false;
    }
}
