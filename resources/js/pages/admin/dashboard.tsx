import { Head } from '@inertiajs/react';
import { Package, Store, Users } from 'lucide-react';
import Heading from '@/components/heading';
import StatCard from '@/components/stat-card';
import admin from '@/routes/admin';

type Props = {
    stats: {
        shopOwners: number;
        shops: number;
        activeShops: number;
        products: number;
    };
};

export default function AdminDashboard({ stats }: Props) {
    return (
        <>
            <Head title="Admin Dashboard" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title="Admin Dashboard"
                    description="Manage shops, products and shop owner accounts."
                />
                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard
                        label="Shops"
                        value={stats.shops}
                        hint={`${stats.activeShops} active`}
                        icon={Store}
                        href={admin.shops.index()}
                    />
                    <StatCard
                        label="Catalog products"
                        value={stats.products}
                        icon={Package}
                        href={admin.products.index()}
                    />
                    <StatCard
                        label="Shop owners"
                        value={stats.shopOwners}
                        icon={Users}
                        href={admin.shopOwners.index()}
                    />
                </div>
            </div>
        </>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: admin.dashboard(),
        },
    ],
};
