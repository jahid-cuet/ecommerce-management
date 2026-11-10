import { Head } from '@inertiajs/react';
import ListingController from '@/actions/App/Http/Controllers/Owner/ListingController';
import Heading from '@/components/heading';
import ListingForm from '@/components/owner/listing-form';
import owner from '@/routes/owner';
import type { Product, ShopSummary } from '@/types';

type Props = {
    shop: ShopSummary;
    product: Product;
};

export default function CreateListing({ shop, product }: Props) {
    return (
        <>
            <Head title={`Add ${product.name}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title={`Add to ${shop.name}`}
                    description="Set the price, details and image customers will see in this shop."
                />
                <ListingForm
                    action={ListingController.store.form(shop.id)}
                    cancelHref={owner.shops.catalog(shop.id)}
                    product={product}
                    submitLabel="Add to shop"
                />
            </div>
        </>
    );
}

CreateListing.layout = ({ shop }: Props) => ({
    breadcrumbs: [
        { title: 'My shops', href: owner.shops.index() },
        { title: shop.name, href: owner.shops.listings.index(shop.id) },
        { title: 'Catalog', href: owner.shops.catalog(shop.id) },
        { title: 'Add product', href: owner.shops.catalog(shop.id) },
    ],
});
