<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case ShopOwner = 'shop_owner';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::ShopOwner => 'Shop Owner',
        };
    }

    /**
     * The route name of this role's dashboard.
     */
    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::ShopOwner => 'owner.dashboard',
        };
    }
}
