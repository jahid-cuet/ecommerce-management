<?php

namespace App\Models;

use App\Support\Thumbnail;
use Carbon\CarbonImmutable;
use Database\Factories\ListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A catalog product assigned to a shop, with shop-specific price, details and thumbnail.
 *
 * @property int $id
 * @property int $shop_id
 * @property int $product_id
 * @property string $price
 * @property string|null $description
 * @property string|null $thumbnail_path
 * @property-read string|null $display_description
 * @property-read string|null $display_thumbnail_url
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Shop $shop
 * @property-read Product $product
 */
#[Fillable(['price', 'description'])]
class Listing extends Model
{
    /** @use HasFactory<ListingFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleted(function (Listing $listing) {
            Thumbnail::delete($listing->thumbnail_path);
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Shop, $this>
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The shop's own details, falling back to the catalog description.
     *
     * @return Attribute<string|null, never>
     */
    protected function displayDescription(): Attribute
    {
        return Attribute::get(fn () => $this->description ?? $this->product->description);
    }

    /**
     * The shop's own thumbnail, falling back to the catalog thumbnail.
     *
     * @return Attribute<string|null, never>
     */
    protected function displayThumbnailUrl(): Attribute
    {
        return Attribute::get(fn () => Thumbnail::url($this->thumbnail_path) ?? $this->product->thumbnail_url);
    }
}
