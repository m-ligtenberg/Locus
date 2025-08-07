<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BookingPolicy
{
    /**
     * Determine whether the user can view any bookings.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own bookings
    }

    /**
     * Determine whether the user can view the booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // Users can view bookings they created or bookings for their properties
        return $user->id === $booking->user_id || 
               $user->id === $booking->property->user_id;
    }

    /**
     * Determine whether the user can create bookings.
     */
    public function create(User $user, ?Property $property = null): bool
    {
        // Any authenticated user can create bookings
        // But they cannot book their own properties
        if ($property) {
            return $user->id !== $property->user_id;
        }
        
        return true;
    }

    /**
     * Determine whether the user can update the booking.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Property owners can manage bookings for their properties
        if ($user->id === $booking->property->user_id) {
            return true;
        }

        // Booking creators can update their own bookings if not confirmed/completed
        return $user->id === $booking->user_id && 
               in_array($booking->status, ['pending']);
    }

    /**
     * Determine whether the user can delete/cancel the booking.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // Property owners can cancel any booking for their properties
        if ($user->id === $booking->property->user_id) {
            return in_array($booking->status, ['pending', 'confirmed']);
        }

        // Booking creators can cancel their own bookings if not completed
        return $user->id === $booking->user_id && 
               in_array($booking->status, ['pending', 'confirmed']);
    }

    /**
     * Determine whether the user can confirm the booking.
     */
    public function confirm(User $user, Booking $booking): bool
    {
        // Only property owners can confirm bookings
        return $user->id === $booking->property->user_id && 
               $booking->status === 'pending';
    }

    /**
     * Determine whether the user can mark the booking as completed.
     */
    public function complete(User $user, Booking $booking): bool
    {
        // Only property owners can mark bookings as completed
        return $user->id === $booking->property->user_id && 
               $booking->status === 'confirmed' &&
               $booking->scheduled_at <= now();
    }

    /**
     * Determine whether the user can reschedule the booking.
     */
    public function reschedule(User $user, Booking $booking): bool
    {
        // Both parties can reschedule if booking is pending or confirmed
        // and scheduled time hasn't passed
        $canManage = $user->id === $booking->user_id || 
                    $user->id === $booking->property->user_id;
                    
        return $canManage && 
               in_array($booking->status, ['pending', 'confirmed']) &&
               $booking->scheduled_at > now();
    }

    /**
     * Determine whether the user can view calendar availability.
     */
    public function viewCalendar(User $user, Property $property): bool
    {
        // Property owners can always view their calendar
        if ($user->id === $property->user_id) {
            return true;
        }

        // Other users can view availability if property is active
        return $property->status === 'active';
    }

    /**
     * Determine whether the user can manage calendar settings.
     */
    public function manageCalendar(User $user, Property $property): bool
    {
        // Only property owners can manage their calendar settings
        return $user->id === $property->user_id;
    }

    /**
     * Determine whether the user can send notifications for the booking.
     */
    public function sendNotification(User $user, Booking $booking): bool
    {
        // Both booking creator and property owner can send notifications
        return $user->id === $booking->user_id || 
               $user->id === $booking->property->user_id;
    }
}