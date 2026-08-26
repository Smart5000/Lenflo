<?php

namespace App\Policies;

use App\Models\Content\ContentItem;
use App\Models\User;

class ContentItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, ContentItem $contentItem): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ContentItem $contentItem): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, ContentItem $contentItem): bool
    {
        return false;
    }

    public function review(User $user, ContentItem $contentItem): bool
    {
        return $user->isSuperAdmin();
    }

    public function approve(User $user, ContentItem $contentItem): bool
    {
        return $user->isSuperAdmin();
    }

    public function reject(User $user, ContentItem $contentItem): bool
    {
        return $user->isSuperAdmin();
    }

    public function publish(User $user, ContentItem $contentItem): bool
    {
        return $user->isSuperAdmin();
    }

    public function disable(User $user, ContentItem $contentItem): bool
    {
        return $user->isSuperAdmin();
    }
}
