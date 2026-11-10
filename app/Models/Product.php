<?php

namespace App\Models;

use App\Support\Thumbnail;
use Carbon\CarbonImmutable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A product in the shared catalog.
 *
 * @property int $id
 * @property int|null $created_by
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $price
 * @property string|null $thumbnail_path
 * @property-read string|null $thumbnail_url
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User|null $creator
 * @property-read Collection<int, Listing> $listings
 */
#[Fillable(['name', 'description', 'price'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            $product->slug ??= static::uniqueSlug($product->name);
        });

        // Delete listings one by one so their thumbnail files are removed too.
        static::deleting(function (Product $product) {
            $product->listings()->get()->each(fn (Listing $listing) => $listing->delete());
        });

        // Remove the image file together with the product.
        static::deleted(function (Product $product) {
            Thumbnail::delete($product->thumbnail_path);
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
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::get(fn () => Thumbnail::url($this->thumbnail_path));
    }

    public static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'product';
        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
