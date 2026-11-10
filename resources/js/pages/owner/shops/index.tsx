import { Head, Link } from '@inertiajs/react';
import { BookOpen, ListChecks } from 'lucide-react';
import Heading from '@/components/heading';
import ShopStatusBadge from '@/components/owner/shop-status-badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import owner from '@/routes/owner';
import type { OwnerShop } from '@/types';

export default function OwnerShopsIndex({ shops }: { shops: OwnerShop[] }) {
    return (
        <>
            <Head title="My shops" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title="My shops"
                    description="Choose a shop to manage the products it sells."
                />

                {shops.length === 0 ? (
                    <p className="rounded-xl border p-6 text-sm text-muted-foreground">
                        You don't have a shop yet. Ask an admin to create one
                        for you.
                    </p>
                ) : (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {shops.map((shop) => (
                            <Card key={shop.id}>
                                <CardHeader>
                                    <div className="flex items-start justify-between gap-2">
                                        <CardTitle>{shop.name}</CardTitle>
                                        <ShopStatusBadge
                                            active={shop.is_active}
                                        />
                                    </div>
                                    <CardDescription>
                                        {shop.listings_count} products listed
                                    </CardDescription>
                                </CardHeader>
                                {shop.description && (
                                    <CardContent className="line-clamp-3 text-sm text-muted-foreground">
                                        {shop.description}
                                    </CardContent>
                                )}
                                <CardFooter className="mt-auto flex flex-wrap gap-2">
                                    <Button asChild size="sm">
                                        <Link
                                            href={owner.shops.listings.index(
                                                shop.id,
                                            )}
                                        >
                                            <ListChecks />
                                            Manage products
                                        </Link>
                                    </Button>
                                    <Button asChild size="sm" variant="outline">
                                        <Link
                                            href={owner.shops.catalog(shop.id)}
                                        >
                                            <BookOpen />
                                            Browse catalog
                                        </Link>
                                    </Button>
                                </CardFooter>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

OwnerShopsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: owner.dashboard() },
        { title: 'My shops', href: owner.shops.index() },
    ],
};
