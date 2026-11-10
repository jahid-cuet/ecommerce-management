<?php

namespace Database\Seeders;

use App\Enums\UserRole;
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
        $this->seedUser('Demo Shop Owner', 'owner@example.com', UserRole::ShopOwner);
    }

    /**
     * Create the user, or update it when it already exists, so seeding can be re-run safely.
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
