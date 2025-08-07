<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return true; // Authenticated users can view their own documents
    }

    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        return $document->canBeAccessedBy($user);
    }

    /**
     * Determine whether the user can create documents.
     */
    public function create(User $user): bool
    {
        return true; // Authenticated users can create documents
    }

    /**
     * Determine whether the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        // Only document owner can update
        if ($document->user_id !== $user->id) {
            return false;
        }

        // Cannot update signed documents
        if ($document->is_signed) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        // Only document owner can delete
        if ($document->user_id !== $user->id) {
            return false;
        }

        // Cannot delete signed documents
        if ($document->is_signed) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can sign the document.
     */
    public function sign(User $user, Document $document): bool
    {
        // Document must be accessible by user
        if (!$document->canBeAccessedBy($user)) {
            return false;
        }

        // Cannot sign already signed documents
        if ($document->is_signed) {
            return false;
        }

        // Cannot sign expired documents
        if ($document->isExpired()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can download the document.
     */
    public function download(User $user, Document $document): bool
    {
        return $document->canBeAccessedBy($user);
    }

    /**
     * Determine whether the user can regenerate the document.
     */
    public function regenerate(User $user, Document $document): bool
    {
        // Only document owner can regenerate
        return $document->user_id === $user->id;
    }
}