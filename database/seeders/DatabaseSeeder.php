<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedUser('Admin', 'admin@example.com', UserRole::Admin);
        $owner = $this->seedUser('Demo Shop Owner', 'owner@example.com', UserRole::ShopOwner);

        Shop::firstOrCreate(
            ['slug' => 'demo-shop'],
            ['owner_id' => $owner->id, 'name' => 'Demo Shop', 'description' => 'A demo shop for trying out the app.', 'is_active' => true],
        );
    }

    /**
     * Create the user, or update it when it already exists, so seeding can be re-run safely.
     */
    private function seedUser(string $name, string $email, UserRole $role): User
    {
        $user = User::firstOrNew(['email' => $email]);

        $user
            ->forceFill([
                'name' => $name,
                'role' => $role,
                'password' => 'password',
                'email_verified_at' => now(),
            ])
            ->save();

        return $user;
    }
}
