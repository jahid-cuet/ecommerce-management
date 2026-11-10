<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed demo data. Every seeder is idempotent, so `php artisan db:seed` can be re-run safely.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CatalogSeeder::class,
            ShopSeeder::class,
        ]);
    }
}
