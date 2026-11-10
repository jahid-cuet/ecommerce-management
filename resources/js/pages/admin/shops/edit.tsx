import { Head } from '@inertiajs/react';
import ShopController from '@/actions/App/Http/Controllers/Admin/ShopController';
import ShopForm from '@/components/admin/shop-form';
import Heading from '@/components/heading';
import admin from '@/routes/admin';
import type { OwnerOption, Shop } from '@/types';

type Props = {
    shop: Shop;
    owners: OwnerOption[];
};

export default function EditShop({ shop, owners }: Props) {
    return (
        <>
            <Head title={`Edit ${shop.name}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title={`Edit ${shop.name}`}
                    description="Update the shop's details, owner or status."
                />
                <ShopForm
                    action={ShopController.update.form(shop.id)}
                    owners={owners}
                    shop={shop}
                    submitLabel="Save changes"
                />
            </div>
        </>
    );
}

EditShop.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: admin.dashboard() },
        { title: 'Shops', href: admin.shops.index() },
        { title: 'Edit', href: admin.shops.index() },
    ],
};
