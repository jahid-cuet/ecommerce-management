import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import SearchForm from '@/components/search-form';
import ListingGrid from '@/components/store/listing-grid';
import { home } from '@/routes';
import store from '@/routes/store';
import type { Paginated, StoreListing, StoreShop } from '@/types';

type Props = {
    shop: StoreShop;
    listings: Paginated<StoreListing>;
    filters: { search: string };
};

export default function StoreShopPage({ shop, listings, filters }: Props) {
    return (
        <>
            <Head title={shop.name} />
            <div className="grid gap-8">
                <section className="grid gap-4">
                    <Link
                        href={home()}
                        className="flex w-fit items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-4" />
                        All shops
                    </Link>
                    <div>
                        <h1 className="text-3xl font-semibold tracking-tight">
                            {shop.name}
                        </h1>
                        {shop.description && (
                            <p className="mt-1 max-w-2xl text-muted-foreground">
                                {shop.description}
                            </p>
                        )}
                    </div>
                    <SearchForm
                        url={store.shops.show.url(shop.slug)}
                        initialValue={filters.search}
                        placeholder={`Search ${shop.name}`}
                    />
                </section>

                <ListingGrid
                    listings={listings}
                    showShop={false}
                    emptyMessage={
                        filters.search
                            ? 'No products match your search.'
                            : "This shop hasn't added any products yet."
                    }
                />
            </div>
        </>
    );
}
