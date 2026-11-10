import { Head } from '@inertiajs/react';
import ShopOwnerController from '@/actions/App/Http/Controllers/Admin/ShopOwnerController';
import ShopOwnerForm from '@/components/admin/shop-owner-form';
import Heading from '@/components/heading';
import admin from '@/routes/admin';
import type { ShopOwner } from '@/types';

type Props = {
    shopOwner: Pick<ShopOwner, 'id' | 'name' | 'email'>;
};

export default function EditShopOwner({ shopOwner }: Props) {
    return (
        <>
            <Head title={`Edit ${shopOwner.name}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title={`Edit ${shopOwner.name}`}
                    description="Update the shop owner's details or set a new password."
                />
                <ShopOwnerForm
                    action={ShopOwnerController.update.form(shopOwner.id)}
                    shopOwner={shopOwner}
                    submitLabel="Save changes"
                />
            </div>
        </>
    );
}

EditShopOwner.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: admin.dashboard() },
        { title: 'Shop owners', href: admin.shopOwners.index() },
        { title: 'Edit', href: admin.shopOwners.index() },
    ],
};
