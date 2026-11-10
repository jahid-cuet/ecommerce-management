import { Head } from '@inertiajs/react';
import ProductController from '@/actions/App/Http/Controllers/Owner/ProductController';
import Heading from '@/components/heading';
import ProductForm from '@/components/products/product-form';
import owner from '@/routes/owner';
import type { Product } from '@/types';

export default function EditOwnerProduct({ product }: { product: Product }) {
    return (
        <>
            <Head title={`Edit ${product.name}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title={`Edit ${product.name}`}
                    description="Changes apply to the shared catalog. Shop prices and details set by each shop stay as they are."
                />
                <ProductForm
                    action={ProductController.update.form(product.id)}
                    cancelHref={owner.products.index()}
                    product={product}
                    submitLabel="Save changes"
                />
            </div>
        </>
    );
}

EditOwnerProduct.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: owner.dashboard() },
        { title: 'My catalog products', href: owner.products.index() },
        { title: 'Edit', href: owner.products.index() },
    ],
};
