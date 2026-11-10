import { Head } from '@inertiajs/react';
import ListingController from '@/actions/App/Http/Controllers/Owner/ListingController';
import Heading from '@/components/heading';
import ListingForm from '@/components/owner/listing-form';
import owner from '@/routes/owner';
import type { Listing, Product, ShopSummary } from '@/types';

type Props = {
    shop: ShopSummary;
    product: Product;
    listing: Listing;
};

export default function EditListing({ shop, product, listing }: Props) {
    return (
        <>
            <Head title={`Edit ${product.name}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title={`Edit ${product.name}`}
                    description={`Changes apply to ${shop.name} only. The shared catalog stays the same.`}
                />
                <ListingForm
                    action={ListingController.update.form([
                        shop.id,
                        listing.id,
                    ])}
                    cancelHref={owner.shops.listings.index(shop.id)}
                    product={product}
                    listing={listing}
                    submitLabel="Save changes"
                />
            </div>
        </>
    );
}

EditListing.layout = ({ shop }: Props) => ({
    breadcrumbs: [
        { title: 'My shops', href: owner.shops.index() },
        { title: shop.name, href: owner.shops.listings.index(shop.id) },
        { title: 'Edit product', href: owner.shops.listings.index(shop.id) },
    ],
});
