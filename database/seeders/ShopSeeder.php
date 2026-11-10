<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo shops and the products they sell.
 *
 * Some products are sold by both active shops at different prices, a few listings have their own
 * details or thumbnail, and "Book Corner" is inactive so customers cannot see it.
 */
class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('email', UserSeeder::OWNER)->firstOrFail();
        $secondOwner = User::where('email', UserSeeder::SECOND_OWNER)->firstOrFail();

        $gadgetHub = $this->seedShop('gadget-hub', $owner, 'Gadget Hub', 'Computer accessories and everyday electronics.', active: true);
        $dailyBazaar = $this->seedShop('daily-bazaar', $secondOwner, 'Daily Bazaar', 'Clothing, home goods and handmade items.', active: true);
        $bookCorner = $this->seedShop('book-corner', $owner, 'Book Corner', 'Opening soon. Inactive, so customers cannot see it yet.', active: false);

        // [shop, product slug, shop price, shop details (null = catalog details), own thumbnail colour]
        $listings = [
            [$gadgetHub, 'wireless-mouse', '1150.00', 'Gadget Hub special: comes with a free mouse pad.', null],
            [$gadgetHub, 'mechanical-keyboard', '4300.00', null, null],
            [$gadgetHub, 'usb-c-charger-65w', '2100.00', null, null],
            [$gadgetHub, 'bluetooth-headphones', '3400.00', null, '#9d174d'],
            [$gadgetHub, 'power-bank-10000mah', '1550.00', null, null],
            [$gadgetHub, 'desk-lamp', '1900.00', null, null],
            [$dailyBazaar, 'wireless-mouse', '1250.00', null, null],
            [$dailyBazaar, 'cotton-t-shirt', '600.00', null, '#15803d'],
            [$dailyBazaar, 'ceramic-coffee-mug', '320.00', 'Hand-painted by local artists. Each mug is unique.', null],
            [$dailyBazaar, 'desk-lamp', '1750.00', null, null],
            [$dailyBazaar, 'handmade-jute-bag', '520.00', null, null],
            [$dailyBazaar, 'power-bank-10000mah', '1650.00', null, null],
            [$bookCorner, 'ceramic-coffee-mug', '300.00', null, null],
        ];

        foreach ($listings as [$shop, $productSlug, $price, $description, $thumbnailColor]) {
            $product = Product::where('slug', $productSlug)->firstOrFail();

            Listing::firstOrNew(['shop_id' => $shop->id, 'product_id' => $product->id])
                ->forceFill([
                    'price' => $price,
                    'description' => $description,
                    'thumbnail_path' => $thumbnailColor === null ? null : DemoImage::store(
                        "listings/demo-{$shop->slug}-{$product->slug}.svg",
                        $product->name,
                        $thumbnailColor,
                        "{$shop->name} edition",
                    ),
                ])
                ->save();
        }
    }

    private function seedShop(string $slug, User $owner, string $name, string $description, bool $active): Shop
    {
        $shop = Shop::firstOrNew(['slug' => $slug]);
        $shop->forceFill([
            'owner_id' => $owner->id,
            'name' => $name,
            'description' => $description,
            'is_active' => $active,
        ])->save();

        return $shop;
    }
}
