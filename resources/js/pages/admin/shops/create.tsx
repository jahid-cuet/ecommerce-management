import { Head } from '@inertiajs/react';
import ShopController from '@/actions/App/Http/Controllers/Admin/ShopController';
import ShopForm from '@/components/admin/shop-form';
import Heading from '@/components/heading';
import admin from '@/routes/admin';
import type { OwnerOption } from '@/types';

export default function CreateShop({ owners }: { owners: OwnerOption[] }) {
    return (
        <>
            <Head title="New shop" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title="New shop"
                    description="Create a shop and choose who owns it."
                />
                <ShopForm
                    action={ShopController.store.form()}
                    owners={owners}
                    submitLabel="Create shop"
                />
            </div>
        </>
    );
}

CreateShop.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: admin.dashboard() },
        { title: 'Shops', href: admin.shops.index() },
        { title: 'New', href: admin.shops.create() },
    ],
};
