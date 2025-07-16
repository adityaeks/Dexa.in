<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_order');
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->can('view_order');
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        return $user->can('create_order');
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->can('update_order');
    }

    /**
     * Determine whether the user can delete the order.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->can('delete_order');
    }

    /**
     * Determine whether the user can restore the order.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->can('restore_order');
    }

    /**
     * Determine whether the user can permanently delete the order.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->can('force_delete_order');
    }
}
