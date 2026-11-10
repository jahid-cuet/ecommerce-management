import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import ShopController from '@/actions/App/Http/Controllers/Admin/ShopController';
import ConfirmDeleteDialog from '@/components/confirm-delete-dialog';
import Heading from '@/components/heading';
import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import admin from '@/routes/admin';
import type { Paginated, ShopListItem } from '@/types';

type Props = {
    shops: Paginated<ShopListItem>;
    filters: { search: string };
};

export default function ShopsIndex({ shops, filters }: Props) {
    const [search, setSearch] = useState(filters.search);

    const submitSearch = (event: FormEvent) => {
        event.preventDefault();
        router.get(
            admin.shops.index.url({ query: { search: search || undefined } }),
            {},
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Shops" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Shops"
                        description="Create shops and assign them to shop owners."
                    />
                    <Button asChild>
                        <Link href={admin.shops.create()}>
                            <Plus />
                            New shop
                        </Link>
                    </Button>
                </div>

                <form onSubmit={submitSearch} className="flex max-w-md gap-2">
                    <Input
                        type="search"
                        name="search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Search by shop or owner name"
                        aria-label="Search shops"
                    />
                    <Button type="submit" variant="outline">
                        <Search />
                        Search
                    </Button>
                </form>

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left text-muted-foreground">
                            <tr>
                                <th className="px-4 py-3 font-medium">Shop</th>
                                <th className="px-4 py-3 font-medium">Owner</th>
                                <th className="px-4 py-3 font-medium">
                                    Status
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Created
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {shops.data.map((shop) => (
                                <tr key={shop.id}>
                                    <td className="px-4 py-3">
                                        <div className="font-medium">
                                            {shop.name}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            /shops/{shop.slug}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <div>{shop.owner.name}</div>
                                        <div className="text-xs text-muted-foreground">
                                            {shop.owner.email}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <Badge
                                            variant={
                                                shop.is_active
                                                    ? 'default'
                                                    : 'secondary'
                                            }
                                        >
                                            {shop.is_active
                                                ? 'Active'
                                                : 'Inactive'}
                                        </Badge>
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {shop.created_at}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link
                                                    href={admin.shops.edit(
                                                        shop.id,
                                                    )}
                                                    aria-label={`Edit ${shop.name}`}
                                                >
                                                    <Pencil />
                                                </Link>
                                            </Button>
                                            <ConfirmDeleteDialog
                                                action={ShopController.destroy.form(
                                                    shop.id,
                                                )}
                                                title={`Delete ${shop.name}?`}
                                                description="This permanently deletes the shop and its product listings."
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label={`Delete ${shop.name}`}
                                                >
                                                    <Trash2 className="text-destructive" />
                                                </Button>
                                            </ConfirmDeleteDialog>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {shops.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="px-4 py-10 text-center text-muted-foreground"
                                    >
                                        {filters.search
                                            ? 'No shops match your search.'
                                            : 'No shops yet.'}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination paginator={shops} />
            </div>
        </>
    );
}

ShopsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: admin.dashboard() },
        { title: 'Shops', href: admin.shops.index() },
    ],
};
