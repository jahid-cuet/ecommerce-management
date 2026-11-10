import { Head, Link, usePage } from '@inertiajs/react';
import { BookOpen, Package, Store } from 'lucide-react';
import Heading from '@/components/heading';
import ShopStatusBadge from '@/components/owner/shop-status-badge';
import StatCard from '@/components/stat-card';
import owner from '@/routes/owner';
import type { OwnerShop } from '@/types';

type Props = {
    stats: {
        shops: number;
        listings: number;
        ownProducts: number;
        catalogProducts: number;
    };
    shops: OwnerShop[];
};

export default function OwnerDashboard({ stats, shops }: Props) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Shop Owner Dashboard" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title={`Welcome, ${auth.user.name}`}
                    description="Manage your shops and their product listings."
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <StatCard
                        label="My shops"
                        value={stats.shops}
                        hint={`${stats.listings} products listed in total`}
                        icon={Store}
                        href={owner.shops.index()}
                    />
                    <StatCard
                        label="My catalog products"
                        value={stats.ownProducts}
                        hint="Products you added to the shared catalog"
                        icon={Package}
                        href={owner.products.index()}
                    />
                    <StatCard
                        label="Shared catalog"
                        value={stats.catalogProducts}
                        hint="Products any shop can list"
                        icon={BookOpen}
                        href={
                            shops.length === 1
                                ? owner.shops.catalog(shops[0].id)
                                : owner.shops.index()
                        }
                    />
                </div>

                <section className="grid gap-2">
                    <h2 className="text-sm font-medium text-muted-foreground">
                        Your shops
                    </h2>
                    {shops.length === 0 ? (
                        <p className="rounded-xl border p-6 text-sm text-muted-foreground">
                            You don't have a shop yet. Ask an admin to create
                            one for you.
                        </p>
                    ) : (
                        <ul className="divide-y rounded-xl border">
                            {shops.map((shop) => (
                                <li key={shop.id}>
                                    <Link
                                        href={owner.shops.listings.index(
                                            shop.id,
                                        )}
                                        className="flex items-center justify-between gap-4 px-4 py-3 hover:bg-muted/50"
                                    >
                                        <span className="font-medium">
                                            {shop.name}
                                        </span>
                                        <span className="flex items-center gap-3 text-sm text-muted-foreground">
                                            {shop.listings_count} products
                                            <ShopStatusBadge
                                                active={shop.is_active}
                                            />
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}

OwnerDashboard.layout = {
    breadcrumbs: [{ title: 'Dashboard', href: owner.dashboard() }],
};
