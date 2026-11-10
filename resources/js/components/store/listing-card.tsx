import { Link } from '@inertiajs/react';
import ProductThumbnail from '@/components/products/product-thumbnail';
import { formatPrice } from '@/lib/format';
import store from '@/routes/store';
import type { StoreListing } from '@/types';

type Props = {
    listing: StoreListing;
    /** Hide the shop name when the card is already shown on that shop's page. */
    showShop?: boolean;
};

/**
 * A product card showing the shop's own price and image.
 */
export default function ListingCard({ listing, showShop = true }: Props) {
    return (
        <Link
            href={store.products.show({
                shop: listing.shop.slug,
                product: listing.product_slug,
            })}
            className="group grid gap-3"
        >
            <ProductThumbnail
                src={listing.thumbnail_url}
                alt={listing.name}
                className="w-full rounded-xl transition-opacity group-hover:opacity-90"
            />
            <div className="grid gap-0.5">
                <h3 className="line-clamp-2 font-medium group-hover:underline">
                    {listing.name}
                </h3>
                {showShop && (
                    <p className="text-sm text-muted-foreground">
                        {listing.shop.name}
                    </p>
                )}
                <p className="font-semibold">{formatPrice(listing.price)}</p>
            </div>
        </Link>
    );
}
