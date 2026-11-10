<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Shop owners can edit catalog products they created. Admins edit any product from the admin panel.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() || $product->created_by === $user->id;
    }
}
