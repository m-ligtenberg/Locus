<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own orders
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create orders
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        // Users can only update their own orders
        // And only if the order is in a modifiable state
        return $user->id === $order->user_id && 
               in_array($order->status, ['pending', 'processing']);
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        // Users can only delete their own orders
        // And only if the order is still pending
        return $user->id === $order->user_id && 
               $order->status === 'pending';
    }

    /**
     * Determine whether the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        // Users can cancel their own orders if not yet paid
        return $user->id === $order->user_id && 
               in_array($order->status, ['pending', 'processing']);
    }

    /**
     * Determine whether the user can request a refund for the order.
     */
    public function refund(User $user, Order $order): bool
    {
        // For now, only allow admin users to process refunds
        // In a real application, you might have different logic
        return $user->hasRole('admin') || 
               ($user->id === $order->user_id && $order->status === 'paid');
    }

    /**
     * Determine whether the user can retry payment for the order.
     */
    public function retryPayment(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && 
               in_array($order->status, ['pending', 'cancelled']);
    }

    /**
     * Determine whether the user can view the order invoice.
     */
    public function viewInvoice(User $user, Order $order): bool
    {
        return $user->id === $order->user_id && 
               in_array($order->status, ['paid', 'completed', 'refunded']);
    }
}