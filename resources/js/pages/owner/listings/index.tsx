import { Head, Link } from '@inertiajs/react';
import { BookOpen, Pencil, Trash2 } from 'lucide-react';
import ListingController from '@/actions/App/Http/Controllers/Owner/ListingController';
import ConfirmDeleteDialog from '@/components/confirm-delete-dialog';
import Heading from '@/components/heading';
import ShopStatusBadge from '@/components/owner/shop-status-badge';
import Pagination from '@/components/pagination';
import ProductThumbnail from '@/components/products/product-thumbnail';
import SearchForm from '@/components/search-form';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatPrice } from '@/lib/format';
import owner from '@/routes/owner';
import type { ListingListItem, Paginated, ShopSummary } from '@/types';

type Props = {
    shop: ShopSummary;
    listings: Paginated<ListingListItem>;
    filters: { search: string };
};

export default function ShopListingsIndex({ shop, listings, filters }: Props) {
    return (
        <>
            <Head title={`${shop.name} products`} />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div className="flex items-start gap-3">
                        <Heading
                            title={shop.name}
                            description="Products sold in this shop, with this shop's own prices."
                        />
                        <ShopStatusBadge active={shop.is_active} />
                    </div>
                    <Button asChild>
                        <Link href={owner.shops.catalog(shop.id)}>
                            <BookOpen />
                            Add from catalog
                        </Link>
                    </Button>
                </div>

                {!shop.is_active && (
                    <p className="rounded-xl border border-dashed p-4 text-sm text-muted-foreground">
                        This shop is inactive, so customers can't see it yet. An
                        admin can activate it.
                    </p>
                )}

                <SearchForm
                    url={owner.shops.listings.index.url(shop.id)}
                    initialValue={filters.search}
                    placeholder="Search this shop's products"
                />

                <div className="overflow-x-auto rounded-xl border">
                    <table className="w-full text-sm">
                        <thead className="bg-muted/50 text-left text-muted-foreground">
                            <tr>
                                <th className="px-4 py-3 font-medium">
                                    Product
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Shop price
                                </th>
                                <th className="px-4 py-3 font-medium">
                                    Catalog price
                                </th>
                                <th className="px-4 py-3 text-right font-medium">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {listings.data.map((listing) => (
                                <tr key={listing.id}>
                                    <td className="px-4 py-3">
                                        <div className="flex items-center gap-3">
                                            <ProductThumbnail
                                                src={listing.thumbnail_url}
                                                alt={listing.name}
                                                className="size-10"
                                            />
                                            <div className="grid gap-1">
                                                <span className="font-medium">
                                                    {listing.name}
                                                </span>
                                                {(listing.has_custom_details ||
                                                    listing.has_custom_thumbnail) && (
                                                    <span className="flex gap-1">
                                                        {listing.has_custom_details && (
                                                            <Badge variant="outline">
                                                                Own details
                                                            </Badge>
                                                        )}
                                                        {listing.has_custom_thumbnail && (
                                                            <Badge variant="outline">
                                                                Own image
                                                            </Badge>
                                                        )}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-4 py-3 font-medium">
                                        {formatPrice(listing.price)}
                                    </td>
                                    <td className="px-4 py-3 text-muted-foreground">
                                        {formatPrice(listing.catalog_price)}
                                    </td>
                                    <td className="px-4 py-3">
                                        <div className="flex justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link
                                                    href={owner.shops.listings.edit(
                                                        [shop.id, listing.id],
                                                    )}
                                                    aria-label={`Edit ${listing.name}`}
                                                >
                                                    <Pencil />
                                                </Link>
                                            </Button>
                                            <ConfirmDeleteDialog
                                                action={ListingController.destroy.form(
                                                    [shop.id, listing.id],
                                                )}
                                                title={`Remove ${listing.name}?`}
                                                description={`The product is removed from ${shop.name} only. It stays in the shared catalog.`}
                                            >
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label={`Remove ${listing.name}`}
                                                >
                                                    <Trash2 className="text-destructive" />
                                                </Button>
                                            </ConfirmDeleteDialog>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {listings.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="px-4 py-10 text-center text-muted-foreground"
                                    >
                                        {filters.search ? (
                                            'No products match your search.'
                                        ) : (
                                            <>
                                                No products yet.{' '}
                                                <Link
                                                    href={owner.shops.catalog(
                                                        shop.id,
                                                    )}
                                                    className="underline underline-offset-4"
                                                >
                                                    Add some from the catalog
                                                </Link>
                                                .
                                            </>
                                        )}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination paginator={listings} />
            </div>
        </>
    );
}

ShopListingsIndex.layout = ({ shop }: Props) => ({
    breadcrumbs: [
        { title: 'My shops', href: owner.shops.index() },
        { title: shop.name, href: owner.shops.listings.index(shop.id) },
    ],
});
