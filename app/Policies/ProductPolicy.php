<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Allow any authenticated user to view the product list
        return $user->is_admin || $user;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        // Allow any authenticated user to view the product list
        return $user->is_admin || $user;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Allow any authenticated user to view the product list
        return $user->is_admin || $user;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        // Check if a user is logged in
        return auth()->check();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        // Only allow administrators or the product's owner to delete it
        return $user->is_admin || $user->id === $product->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        // Only allow administrators or the product's owner to restore it
        return $user->is_admin || $user->id === $product->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        // Only allow administrators to permanently delete
        return $user->is_admin;
    }
}
