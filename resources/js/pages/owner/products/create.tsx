import { Head } from '@inertiajs/react';
import ProductController from '@/actions/App/Http/Controllers/Owner/ProductController';
import Heading from '@/components/heading';
import ProductForm from '@/components/products/product-form';
import owner from '@/routes/owner';

type Props = {
    /** Set when the owner started from a shop's catalog page. */
    shop: { id: number; name: string } | null;
};

export default function CreateOwnerProduct({ shop }: Props) {
    return (
        <>
            <Head title="New catalog product" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title="New catalog product"
                    description={
                        shop
                            ? `The product joins the shared catalog. Next, you'll set its price for ${shop.name}.`
                            : 'The product joins the shared catalog, so any shop can list it.'
                    }
                />
                <ProductForm
                    action={ProductController.store.form(
                        shop ? { query: { shop: shop.id } } : undefined,
                    )}
                    cancelHref={
                        shop
                            ? owner.shops.catalog(shop.id)
                            : owner.products.index()
                    }
                    submitLabel={
                        shop ? 'Create and continue' : 'Create product'
                    }
                />
            </div>
        </>
    );
}

CreateOwnerProduct.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: owner.dashboard() },
        { title: 'My catalog products', href: owner.products.index() },
        { title: 'New', href: owner.products.create() },
    ],
};
