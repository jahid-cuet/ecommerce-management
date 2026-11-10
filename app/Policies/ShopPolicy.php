<?php

namespace App\Policies;

use App\Models\Shop;
use App\Models\User;

class ShopPolicy
{
    /**
     * Shop owners can only manage the listings of their own shops.
     */
    public function manage(User $user, Shop $shop): bool
    {
        return $user->isShopOwner() && $shop->owner_id === $user->id;
    }
}
