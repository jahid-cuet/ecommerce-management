import { Form, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import ProductThumbnail from '@/components/products/product-thumbnail';
import ThumbnailInput from '@/components/products/thumbnail-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { formatPrice } from '@/lib/format';
import type { Listing, Product } from '@/types';
import type { RouteDefinition, RouteFormDefinition } from '@/wayfinder';

type Props = {
    action: RouteFormDefinition<'post'>;
    cancelHref: RouteDefinition<'get'>;
    /** The catalog product; its values are the defaults for the shop. */
    product: Product;
    listing?: Listing;
    submitLabel: string;
};

/**
 * Sets a shop's own price, details and thumbnail for a catalog product.
 */
export default function ListingForm({
    action,
    cancelHref,
    product,
    listing,
    submitLabel,
}: Props) {
    return (
        <div className="grid max-w-xl gap-6">
            <div className="flex items-center gap-4 rounded-xl border p-4">
                <ProductThumbnail
                    src={product.thumbnail_url}
                    alt={product.name}
                    className="size-16"
                />
                <div className="min-w-0">
                    <p className="text-xs text-muted-foreground">
                        Catalog product
                    </p>
                    <p className="truncate font-medium">{product.name}</p>
                    <p className="text-sm text-muted-foreground">
                        Suggested price {formatPrice(product.price)}
                    </p>
                </div>
            </div>

            <Form {...action} className="space-y-6">
                {({ processing, errors }) => (
                    <>
                        {!listing && (
                            <input
                                type="hidden"
                                name="product_id"
                                value={product.id}
                            />
                        )}
                        <InputError message={errors.product_id} />

                        <div className="grid gap-2">
                            <Label htmlFor="price">Your price (BDT)</Label>
                            <Input
                                id="price"
                                name="price"
                                type="number"
                                inputMode="decimal"
                                min="0"
                                step="0.01"
                                defaultValue={listing?.price ?? product.price}
                                required
                                autoFocus
                            />
                            <InputError message={errors.price} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="description">Shop details</Label>
                            <Textarea
                                id="description"
                                name="description"
                                defaultValue={listing?.description ?? ''}
                                rows={5}
                                placeholder={
                                    product.description ??
                                    'Describe the product for your customers'
                                }
                            />
                            <p className="text-xs text-muted-foreground">
                                Leave empty to show the catalog description.
                            </p>
                            <InputError message={errors.description} />
                        </div>

                        <ThumbnailInput
                            label="Shop thumbnail"
                            currentUrl={listing?.thumbnail_url}
                            fallbackUrl={product.thumbnail_url}
                            hint="Optional. Without one, the catalog image is shown. JPG, PNG or WebP, up to 2 MB."
                            error={errors.thumbnail}
                        />

                        <div className="flex items-center gap-2">
                            <Button type="submit" disabled={processing}>
                                {processing && <Spinner />}
                                {submitLabel}
                            </Button>
                            <Button variant="ghost" asChild>
                                <Link href={cancelHref}>Cancel</Link>
                            </Button>
                        </div>
                    </>
                )}
            </Form>
        </div>
    );
}
