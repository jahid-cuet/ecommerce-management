import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import type { FormEvent } from 'react';
import { useState } from 'react';
import ProductController from '@/actions/App/Http/Controllers/Admin/ProductController';
import ConfirmDeleteDialog from '@/components/confirm-delete-dialog';
import Heading from '@/components/heading';
import Pagination from '@/components/pagination';
import ProductThumbnail from '@/components/products/product-thumbnail';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatPrice } from '@/lib/format';
import admin from '@/routes/admin';
import type { Paginated, ProductListItem } from '@/types';

type Props = {
    products: Paginated<ProductListItem>;
    filters: { search: string };
};

export default function ProductsIndex({ products, filters }: Props) {
    const [search, setSearch] = useState(filters.search);

    const submitSearch = (event: FormEvent) => {
        event.preventDefault();
        router.get(
            admin.products.index.url({
                query: { search: search || undefined },
            }),
            {},
            { preserveState: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Products" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Products"
                        description="The shared catalog. Every shop can list these products."
                    />
                    <Button asChild>
                        <Link href={admin.products.create()}>
                            <Plus />
                            New product
                        </Link>
                    </Button>
                </div>

                <form onSubmit={submitSearch} className="flex max-w-md gap-2">
                    <Input
                        type="search"
                        name="search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Search by product name"
                        aria-label="Search products"
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
                                <th className="px-4 py-3 font-medium">
                                    Product
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Suggested price
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Added by
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {products.data.map((product) => (
                                <tr key={product.id}>
                                    <td className="px-4 py-3">
                                        <div className="flex items-center gap-3">
                                            <ProductThumbnail
                                                src={product.thumbnail_url}
                                                alt={product.name}
                                                className="size-10"
                                            />
                                            <span className="font-medium">
                                                {product.name}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        {formatPrice(product.price)}
                                    </td>
                                    <td className="px-4 py-3">
                                        {product.creator ? (
                                            <>
                                                <div>
                                                    {product.creator.name}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    {product.creator.role}
                                                </div>
                                            </>
                                        ) : (
                                            <span className="text-muted-foreground">
                                                Deleted account
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link
                                                    href={admin.products.edit(
                                                        product.id,
                                                    )}
                                                    aria-label={`Edit ${product.name}`}
                                                >
                                                    <Pencil />
                                                </Link>
                                            </Button>
                                            <ConfirmDeleteDialog
                                                action={ProductController.destroy.form(
                                                    product.id,
                                                )}
                                                title={`Delete ${product.name}?`}
                                                description="This removes the product from the catalog and from every shop that lists it."
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label={`Delete ${product.name}`}
                                                >
                                                    <Trash2 className="text-destructive" />
                                                </Button>
                                            </ConfirmDeleteDialog>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {products.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="px-4 py-10 text-center text-muted-foreground"
                                    >
                                        {filters.search
                                            ? 'No products match your search.'
                                            : 'No products yet.'}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination paginator={products} />
            </div>
        </>
    );
}

ProductsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: admin.dashboard() },
        { title: 'Products', href: admin.products.index() },
    ],
};
