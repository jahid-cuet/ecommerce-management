import { Head, Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { Package, Store, Users } from 'lucide-react';
import Heading from '@/components/heading';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import admin from '@/routes/admin';
import type { RouteDefinition } from '@/wayfinder';

type Props = {
    stats: {
        shopOwners: number;
        shops: number;
        activeShops: number;
        products: number;
    };
};

function StatCard({
    label,
    value,
    hint,
    icon: Icon,
    href,
}: {
    label: string;
    value: number;
    hint?: string;
    icon: LucideIcon;
    href: RouteDefinition<'get'>;
}) {
    return (
        <Link href={href}>
            <Card className="h-full transition-colors hover:bg-muted/50">
                <CardHeader>
                    <CardDescription className="flex items-center gap-2">
                        <Icon className="size-4" />
                        {label}
                    </CardDescription>
                    <CardTitle className="text-3xl">{value}</CardTitle>
                    {hint && (
                        <p className="text-xs text-muted-foreground">{hint}</p>
                    )}
                </CardHeader>
            </Card>
        </Link>
    );
}

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
