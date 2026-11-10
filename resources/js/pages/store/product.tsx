import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import ProductThumbnail from '@/components/products/product-thumbnail';
import { formatPrice } from '@/lib/format';
import store from '@/routes/store';
import type { StoreListing, StoreListingDetail } from '@/types';

type Props = {
    listing: StoreListingDetail;
    otherShops: { shop: StoreListing['shop']; price: string }[];
};

export default function StoreProduct({ listing, otherShops }: Props) {
    return (
        <>
            <Head title={`${listing.name} - ${listing.shop.name}`} />
            <div className="grid gap-6">
                <Link
                    href={store.shops.show(listing.shop.slug)}
                    className="flex w-fit items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-4" />
                    Back to {listing.shop.name}
                </Link>

                <div className="grid gap-8 md:grid-cols-2">
                    <ProductThumbnail
                        src={listing.thumbnail_url}
                        alt={listing.name}
                        className="w-full rounded-2xl"
                    />

                    <div className="grid content-start gap-6">
                        <div className="grid gap-2">
                            <Link
                                href={store.shops.show(listing.shop.slug)}
                                className="w-fit text-sm text-muted-foreground hover:underline"
                            >
                                Sold by {listing.shop.name}
                            </Link>
                            <h1 className="text-3xl font-semibold tracking-tight">
                                {listing.name}
                            </h1>
                            <p className="text-2xl font-semibold">
                                {formatPrice(listing.price)}
                            </p>
                        </div>

                        {listing.description && (
                            <p className="leading-relaxed whitespace-pre-line text-muted-foreground">
                                {listing.description}
                            </p>
                        )}

                        {otherShops.length > 0 && (
                            <section className="grid gap-2">
                                <h2 className="text-sm font-medium">
                                    Also available at
                                </h2>
                                <ul className="divide-y rounded-xl border">
                                    {otherShops.map((other) => (
                                        <li key={other.shop.slug}>
                                            <Link
                                                href={store.products.show({
                                                    shop: other.shop.slug,
                                                    product:
                                                        listing.product_slug,
                                                })}
                                                className="flex items-center justify-between gap-4 px-4 py-3 text-sm hover:bg-muted/50"
                                            >
                                                <span>{other.shop.name}</span>
                                                <span className="font-medium">
                                                    {formatPrice(other.price)}
                                                </span>
                                            </Link>
                                        </li>
                                    ))}
                                </ul>
                            </section>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
