import { Head } from '@inertiajs/react';
import ShopOwnerController from '@/actions/App/Http/Controllers/Admin/ShopOwnerController';
import ShopOwnerForm from '@/components/admin/shop-owner-form';
import Heading from '@/components/heading';
import admin from '@/routes/admin';

export default function CreateShopOwner() {
    return (
        <>
            <Head title="New shop owner" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title="New shop owner"
                    description="The shop owner can log in with this email and password."
                />
                <ShopOwnerForm
                    action={ShopOwnerController.store.form()}
                    submitLabel="Create shop owner"
                />
            </div>
        </>
    );
}

CreateShopOwner.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: admin.dashboard() },
        { title: 'Shop owners', href: admin.shopOwners.index() },
        { title: 'New', href: admin.shopOwners.create() },
    ],
};
