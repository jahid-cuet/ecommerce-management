import { Head } from '@inertiajs/react';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import Heading from '@/components/heading';
import ProductForm from '@/components/products/product-form';
import admin from '@/routes/admin';
import type { Product } from '@/types';

export default function EditProduct({ product }: { product: Product }) {
    return (
        <>
            <Head title={`Edit ${product.name}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title={`Edit ${product.name}`}
                    description="Changes apply to the shared catalog."
                />
                <ProductForm
                    action={ProductController.update.form(product.id)}
                    cancelHref={admin.products.index()}
                    product={product}
                    submitLabel="Save changes"
                />
            </div>
        </>
    );
}

EditProduct.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: admin.dashboard() },
        { title: 'Products', href: admin.products.index() },
        { title: 'Edit', href: admin.products.index() },
    ],
};
