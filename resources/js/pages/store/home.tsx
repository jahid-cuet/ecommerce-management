import { Head, Link } from '@inertiajs/react';
import { Store } from 'lucide-react';
import SearchForm from '@/components/search-form';
import ListingGrid from '@/components/store/listing-grid';
import { home } from '@/routes';
import store from '@/routes/store';
import type { Paginated, StoreListing, StoreShopCard } from '@/types';

type Props = {
    shops: StoreShopCard[];
    listings: Paginated<StoreListing>;
    filters: { search: string };
};

export default function StoreHome({ shops, listings, filters }: Props) {
    return (
        <>
            <Head title="Shop" />
            <div className="grid gap-10">
                <section className="grid gap-4">
                    <div>
                        <h1 className="text-3xl font-semibold tracking-tight">
                            Shop from our stores
                        </h1>
                        <p className="mt-1 text-muted-foreground">
                            Every shop sets its own prices. Browse a shop or
                            search all products.
                        </p>
                    </div>
                    <SearchForm
                        url={home.url()}
                        initialValue={filters.search}
                        placeholder="Search products"
                    />
                </section>

                {shops.length > 0 && !filters.search && (
                    <section className="grid gap-4">
                        <h2 className="text-lg font-semibold">Shops</h2>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {shops.map((shop) => (
                                <Link
                                    key={shop.slug}
                                    href={store.shops.show(shop.slug)}
                                    className="flex gap-3 rounded-xl border p-4 transition-colors hover:bg-muted/50"
                                >
                                    <span className="flex size-10 shrink-0 items-center justify-center rounded-lg bg-muted">
                                        <Store className="size-5" />
                                    </span>
                                    <span className="grid gap-0.5">
                                        <span className="font-medium">
                                            {shop.name}
                                        </span>
                                        <span className="text-sm text-muted-foreground">
                                            {shop.listings_count === 1
                                                ? '1 product'
                                                : `${shop.listings_count} products`}
                                        </span>
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </section>
                )}

                <section className="grid gap-4">
                    <h2 className="text-lg font-semibold">
                        {filters.search
                            ? `Results for "${filters.search}"`
                            : 'Latest products'}
                    </h2>
                    <ListingGrid
                        listings={listings}
                        emptyMessage={
                            filters.search
                                ? 'No products match your search.'
                                : 'No products are for sale yet.'
                        }
                    />
                </section>
            </div>
        </>
    );
}
