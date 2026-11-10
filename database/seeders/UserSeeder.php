<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo accounts. Every password is "password".
 */
class UserSeeder extends Seeder
{
    public const ADMIN = 'admin@example.com';

    public const OWNER = 'owner@example.com';

    public const SECOND_OWNER = 'owner2@example.com';

    public function run(): void
    {
        $this->seedUser('Admin', self::ADMIN, UserRole::Admin);
        $this->seedUser('Rahim Ahmed', self::OWNER, UserRole::ShopOwner);
        $this->seedUser('Karim Hossain', self::SECOND_OWNER, UserRole::ShopOwner);
    }

    /**
     * Create the user, or update it when it already exists.
     */
    private function seedUser(string $name, string $email, UserRole $role): void
    {
        User::firstOrNew(['email' => $email])
            ->forceFill([
                'name' => $name,
                'role' => $role,
                'password' => 'password',
                'email_verified_at' => now(),
            ])
            ->save();
    }
}
