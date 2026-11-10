import { Head } from '@inertiajs/react';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import Heading from '@/components/heading';
import ProductForm from '@/components/products/product-form';
import admin from '@/routes/admin';

export default function CreateProduct() {
    return (
        <>
            <Head title="New product" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title="New product"
                    description="Add a product to the shared catalog."
                />
                <ProductForm
                    action={ProductController.store.form()}
                    cancelHref={admin.products.index()}
                    submitLabel="Create product"
                />
            </div>
        </>
    );
}

CreateProduct.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: admin.dashboard() },
        { title: 'Products', href: admin.products.index() },
        { title: 'New', href: admin.products.create() },
    ],
};
