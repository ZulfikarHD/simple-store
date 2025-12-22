<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

/**
 * OrderPolicy untuk mengelola authorization akses order
 * dengan pemisahan akses customer dan admin
 */
class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     * Hanya admin yang bisa melihat semua orders
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the order.
     * User bisa view order miliknya, admin bisa view semua
     */
    public function view(User $user, Order $order): bool
    {
        // Admin bisa view semua order
        if ($user->isAdmin()) {
            return true;
        }

        // User biasa hanya bisa view order miliknya sendiri
        // dan order harus memiliki user_id (tidak null untuk guest orders)
        return $order->user_id === $user->id;
    }

    /**
     * Determine whether the user can update order.
     * Hanya admin yang bisa update order (status, dll)
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete order.
     * Hanya admin yang bisa delete order
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
