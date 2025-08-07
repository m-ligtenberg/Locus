<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ServicePolicy
{
    /**
     * Determine whether the user can view any services.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the service.
     */
    public function view(?User $user, Service $service): bool
    {
        return $service->is_active || ($user && $user->is_admin);
    }

    /**
     * Determine whether the user can create services.
     */
    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the service.
     */
    public function update(User $user, Service $service): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the service.
     */
    public function delete(User $user, Service $service): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the service.
     */
    public function restore(User $user, Service $service): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the service.
     */
    public function forceDelete(User $user, Service $service): bool
    {
        return $user->is_admin;
    }
}