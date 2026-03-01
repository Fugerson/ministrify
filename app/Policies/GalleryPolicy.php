<?php

namespace App\Policies;

use App\Models\Gallery;
use App\Models\User;

class GalleryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canView('website');
    }

    public function view(User $user, Gallery $gallery): bool
    {
        return $user->church_id === $gallery->church_id;
    }

    public function create(User $user): bool
    {
        return $user->canCreate('website');
    }

    public function update(User $user, Gallery $gallery): bool
    {
        if ($user->church_id !== $gallery->church_id) {
            return false;
        }

        if ($user->canEdit('website')) {
            return true;
        }

        // Creator can update their own gallery
        return $gallery->created_by === $user->id;
    }

    public function delete(User $user, Gallery $gallery): bool
    {
        if ($user->church_id !== $gallery->church_id) {
            return false;
        }

        if ($user->canDelete('website')) {
            return true;
        }

        // Creator can delete their own gallery
        return $gallery->created_by === $user->id;
    }
}
