import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import ShopOwnerController from '@/actions/App/Http/Controllers/Admin/ShopOwnerController';
import ConfirmDeleteDialog from '@/components/confirm-delete-dialog';
import Heading from '@/components/heading';
import Pagination from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import admin from '@/routes/admin';
import type { Paginated, ShopOwner } from '@/types';

type Props = {
    shopOwners: Paginated<ShopOwner>;
    filters: { search: string };
};

export default function ShopOwnersIndex({ shopOwners, filters }: Props) {
    const [search, setSearch] = useState(filters.search);

    const submitSearch = (event: FormEvent) => {
        event.preventDefault();
        router.get(
            admin.shopOwners.index.url({
                query: { search: search || undefined },
            }),
            {},
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Shop owners" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Shop owners"
                        description="Create and manage shop owner accounts."
                    />
                    <Button asChild>
                        <Link href={admin.shopOwners.create()}>
                            <Plus />
                            New shop owner
                        </Link>
                    </Button>
                </div>

                <form onSubmit={submitSearch} className="flex max-w-md gap-2">
                    <Input
                        type="search"
                        name="search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Search by name or email"
                        aria-label="Search shop owners"
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
                                <th className="px-4 py-3 font-medium">Name</th>
                                <th className="px-4 py-3 font-medium">Email</th>
                                <th className="px-4 py-3 font-medium">Shops</th>
                                <th className="px-4 py-3 font-medium">
                                    Created
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {shopOwners.data.map((owner) => (
                                <tr key={owner.id}>
                                    <td className="px-4 py-3 font-medium">
                                        {owner.name}
                                    </td>
                                    <td className="px-4 py-3">{owner.email}</td>
                                    <td className="px-4 py-3">
                                        {owner.shops_count}
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {owner.created_at}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link
                                                    href={admin.shopOwners.edit(
                                                        owner.id,
                                                    )}
                                                    aria-label={`Edit ${owner.name}`}
                                                >
                                                    <Pencil />
                                                </Link>
                                            </Button>
                                            <ConfirmDeleteDialog
                                                action={ShopOwnerController.destroy.form(
                                                    owner.id,
                                                )}
                                                title={`Delete ${owner.name}?`}
                                                description={
                                                    owner.shops_count > 0
                                                        ? `${owner.name} still owns ${owner.shops_count} shop(s). Reassign or delete those shops first.`
                                                        : "This permanently deletes the shop owner's account."
                                                }
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label={`Delete ${owner.name}`}
                                                >
                                                    <Trash2 className="text-destructive" />
                                                </Button>
                                            </ConfirmDeleteDialog>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {shopOwners.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="px-4 py-10 text-center text-muted-foreground"
                                    >
                                        {filters.search
                                            ? 'No shop owners match your search.'
                                            : 'No shop owners yet.'}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination paginator={shopOwners} />
            </div>
        </>
    );
}

ShopOwnersIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: admin.dashboard() },
        { title: 'Shop owners', href: admin.shopOwners.index() },
    ],
};
