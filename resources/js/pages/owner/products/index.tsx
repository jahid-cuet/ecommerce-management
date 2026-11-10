import { Head, Link } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import Heading from '@/components/heading';
import Pagination from '@/components/pagination';
import ProductThumbnail from '@/components/products/product-thumbnail';
import SearchForm from '@/components/search-form';
import { Button } from '@/components/ui/button';
import { formatPrice } from '@/lib/format';
import owner from '@/routes/owner';
import type { OwnProductListItem, Paginated } from '@/types';

type Props = {
    products: Paginated<OwnProductListItem>;
    filters: { search: string };
};

export default function OwnerProductsIndex({ products, filters }: Props) {
    return (
        <>
            <Head title="My catalog products" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="My catalog products"
                        description="Products you added to the shared catalog. Any shop can list them."
                    />
                    <Button asChild>
                        <Link href={owner.products.create()}>
                            <Plus />
                            New product
                        </Link>
                    </Button>
                </div>

                <SearchForm
                    url={owner.products.index.url()}
                    initialValue={filters.search}
                    placeholder="Search your products"
                />

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
                                    Listed in
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
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {product.listings_count === 1
                                            ? '1 shop'
                                            : `${product.listings_count} shops`}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex justify-end">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link
                                                    href={owner.products.edit(
                                                        product.id,
                                                    )}
                                                    aria-label={`Edit ${product.name}`}
                                                >
                                                    <Pencil />
                                                </Link>
                                            </Button>
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
                                            : "You haven't added any products to the catalog yet."}
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

OwnerProductsIndex.layout = {
    breadcrumbs: [
        { title: 'Dashboard', href: owner.dashboard() },
        { title: 'My catalog products', href: owner.products.index() },
    ],
};
