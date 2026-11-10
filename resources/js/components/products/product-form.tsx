import { Form, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import ThumbnailInput from '@/components/products/thumbnail-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import type { Product } from '@/types';
import type { RouteDefinition, RouteFormDefinition } from '@/wayfinder';

type Props = {
    action: RouteFormDefinition<'post'>;
    cancelHref: RouteDefinition<'get'>;
    product?: Product;
    submitLabel: string;
};

/**
 * Creates or edits a catalog product. Used by both admins and shop owners.
 */
export default function ProductForm({
    action,
    cancelHref,
    product,
    submitLabel,
}: Props) {
    return (
        <Form {...action} className="max-w-xl space-y-6">
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="name">Product name</Label>
                        <Input
                            id="name"
                            name="name"
                            defaultValue={product?.name}
                            required
                            autoFocus
                            placeholder="e.g. Wireless Mouse"
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="price">Price (BDT)</Label>
                        <Input
                            id="price"
                            name="price"
                            type="number"
                            inputMode="decimal"
                            min="0"
                            step="0.01"
                            defaultValue={product?.price}
                            required
                            placeholder="0.00"
                        />
                        <p className="text-xs text-muted-foreground">
                            Suggested price. Each shop can set its own price.
                        </p>
                        <InputError message={errors.price} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            id="description"
                            name="description"
                            defaultValue={product?.description ?? ''}
                            rows={5}
                            placeholder="Describe the product"
                        />
                        <InputError message={errors.description} />
                    </div>

                    <ThumbnailInput
                        currentUrl={product?.thumbnail_url}
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
    );
}
