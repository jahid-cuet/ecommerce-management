import { Head, Link } from '@inertiajs/react';
import { Check, Pencil, Plus } from 'lucide-react';
import Heading from '@/components/heading';
import Pagination from '@/components/pagination';
import ProductThumbnail from '@/components/products/product-thumbnail';
import SearchForm from '@/components/search-form';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { formatPrice } from '@/lib/format';
import owner from '@/routes/owner';
import type { CatalogProduct, Paginated, ShopSummary } from '@/types';

type Props = {
    shop: ShopSummary;
    products: Paginated<CatalogProduct>;
    filters: { search: string };
};

export default function ShopCatalog({ shop, products, filters }: Props) {
    return (
        <>
            <Head title={`Catalog for ${shop.name}`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <Heading
                        title="Shared catalog"
                        description={`Pick products to sell in ${shop.name}. You set the shop's own price, details and image.`}
                    />
                    <Button asChild variant="outline">
                        <Link
                            href={owner.products.create({
                                query: { shop: shop.id },
                            })}
                        >
                            <Plus />
                            New catalog product
                        </Link>
                    </Button>
                </div>

                <SearchForm
                    url={owner.shops.catalog.url(shop.id)}
                    initialValue={filters.search}
                    placeholder="Search the catalog"
                />

                {products.data.length === 0 ? (
                    <p className="rounded-xl border p-10 text-center text-sm text-muted-foreground">
                        {filters.search
                            ? 'No catalog products match your search.'
                            : 'The catalog is empty. Create the first product.'}
                    </p>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {products.data.map((product) => (
                            <Card key={product.id} className="gap-4 pt-0">
                                <ProductThumbnail
                                    src={product.thumbnail_url}
                                    alt={product.name}
                                    className="w-full rounded-b-none border-0 border-b"
                                />
                                <CardContent className="grid gap-1">
                                    <p className="font-medium">
                                        {product.name}
                                    </p>
                                    <p className="text-sm">
                                        {formatPrice(product.price)}
                                        <span className="text-muted-foreground">
                                            {' '}
                                            suggested
                                        </span>
                                    </p>
                                    {product.description && (
                                        <p className="line-clamp-2 text-sm text-muted-foreground">
                                            {product.description}
                                        </p>
                                    )}
                                    {product.added_by && (
                                        <p className="text-xs text-muted-foreground">
                                            Added by {product.added_by}
                                        </p>
                                    )}
                                </CardContent>
                                <CardFooter className="mt-auto">
                                    {product.listing_id ? (
                                        <Button
                                            asChild
                                            variant="secondary"
                                            size="sm"
                                            className="w-full"
                                        >
                                            <Link
                                                href={owner.shops.listings.edit(
                                                    [
                                                        shop.id,
                                                        product.listing_id,
                                                    ],
                                                )}
                                            >
                                                <Check />
                                                In your shop
                                                <Pencil className="ml-auto" />
                                            </Link>
                                        </Button>
                                    ) : (
                                        <Button
                                            asChild
                                            size="sm"
                                            className="w-full"
                                        >
                                            <Link
                                                href={owner.shops.listings.create(
                                                    shop.id,
                                                    {
                                                        query: {
                                                            product: product.id,
                                                        },
                                                    },
                                                )}
                                            >
                                                <Plus />
                                                Add to {shop.name}
                                            </Link>
                                        </Button>
                                    )}
                                </CardFooter>
                            </Card>
                        ))}
                    </div>
                )}

                <Pagination paginator={products} />
            </div>
        </>
    );
}

ShopCatalog.layout = ({ shop }: Props) => ({
    breadcrumbs: [
        { title: 'My shops', href: owner.shops.index() },
        { title: shop.name, href: owner.shops.listings.index(shop.id) },
        { title: 'Catalog', href: owner.shops.catalog(shop.id) },
    ],
});
