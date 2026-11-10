<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The shared product catalog. Products come from the admin and from both shop owners.
 * "Steel Water Bottle" and "Notebook Set" are not listed by any shop, so customers never see them.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $creators = User::whereIn('email', [UserSeeder::ADMIN, UserSeeder::OWNER, UserSeeder::SECOND_OWNER])
            ->pluck('id', 'email');

        $products = [
            // [name, suggested price, colour, creator email, description]
            ['Wireless Mouse', '1200.00', '#2563eb', UserSeeder::ADMIN, 'A quiet 2.4 GHz wireless mouse with a USB receiver and up to 12 months of battery life.'],
            ['Mechanical Keyboard', '4500.00', '#7c3aed', UserSeeder::ADMIN, 'Full-size keyboard with tactile switches and white backlighting.'],
            ['USB-C Charger 65W', '2200.00', '#0891b2', UserSeeder::ADMIN, 'Fast charger for laptops and phones, with a 1.5 m USB-C cable.'],
            ['Bluetooth Headphones', '3500.00', '#db2777', UserSeeder::ADMIN, 'Over-ear headphones with 30 hours of playback and a built-in microphone.'],
            ['Cotton T-Shirt', '650.00', '#16a34a', UserSeeder::ADMIN, '100% cotton, regular fit. Available in M, L and XL.'],
            ['Ceramic Coffee Mug', '350.00', '#ea580c', UserSeeder::ADMIN, 'A 350 ml mug, microwave and dishwasher safe.'],
            ['Desk Lamp', '1800.00', '#ca8a04', UserSeeder::ADMIN, 'LED desk lamp with three brightness levels and an adjustable arm.'],
            ['Steel Water Bottle', '900.00', '#475569', UserSeeder::ADMIN, 'Keeps drinks cold for 24 hours. Not sold by any shop yet.'],
            ['Power Bank 10000mAh', '1600.00', '#dc2626', UserSeeder::OWNER, 'Slim power bank that charges two devices at once.'],
            ['Handmade Jute Bag', '550.00', '#a16207', UserSeeder::SECOND_OWNER, 'A reusable shopping bag woven from Bangladeshi jute.'],
            ['Notebook Set', '300.00', '#0d9488', UserSeeder::SECOND_OWNER, 'Three A5 ruled notebooks. Not sold by any shop yet.'],
        ];

        foreach ($products as [$name, $price, $color, $creator, $description]) {
            $slug = Str::slug($name);

            Product::firstOrNew(['slug' => $slug])
                ->forceFill([
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'created_by' => $creators[$creator] ?? null,
                    'thumbnail_path' => DemoImage::store("products/demo-{$slug}.svg", $name, $color),
                ])
                ->save();
        }
    }
}
