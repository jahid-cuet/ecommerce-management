import { Form, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import admin from '@/routes/admin';
import type { OwnerOption, Shop } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

type Props = {
    action: RouteFormDefinition<'post'>;
    owners: OwnerOption[];
    shop?: Shop;
    submitLabel: string;
};

export default function ShopForm({ action, owners, shop, submitLabel }: Props) {
    if (owners.length === 0) {
        return (
            <p className="text-sm text-muted-foreground">
                A shop needs an owner.{' '}
                <TextLink href={admin.shopOwners.create()}>
                    Create a shop owner
                </TextLink>{' '}
                first.
            </p>
        );
    }

    return (
        <Form {...action} className="max-w-xl space-y-6">
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="name">Shop name</Label>
                        <Input
                            id="name"
                            name="name"
                            defaultValue={shop?.name}
                            required
                            autoFocus
                            placeholder="e.g. Dhaka Gadget House"
                        />
                        {shop && (
                            <p className="text-xs text-muted-foreground">
                                Shop URL: /shops/{shop.slug}
                            </p>
                        )}
                        <InputError message={errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="owner_id">Shop owner</Label>
                        <Select
                            name="owner_id"
                            defaultValue={
                                shop ? String(shop.owner_id) : undefined
                            }
                        >
                            <SelectTrigger id="owner_id" className="w-full">
                                <SelectValue placeholder="Choose a shop owner" />
                            </SelectTrigger>
                            <SelectContent>
                                {owners.map((owner) => (
                                    <SelectItem
                                        key={owner.id}
                                        value={String(owner.id)}
                                    >
                                        {owner.name} ({owner.email})
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={errors.owner_id} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="description">Description</Label>
                        <Textarea
                            id="description"
                            name="description"
                            defaultValue={shop?.description ?? ''}
                            rows={4}
                            placeholder="What does this shop sell?"
                        />
                        <InputError message={errors.description} />
                    </div>

                    <div className="flex items-start gap-3">
                        <Checkbox
                            id="is_active"
                            name="is_active"
                            value="1"
                            defaultChecked={shop?.is_active ?? true}
                        />
                        <div className="grid gap-1">
                            <Label htmlFor="is_active">Active</Label>
                            <p className="text-xs text-muted-foreground">
                                Inactive shops are hidden from customers.
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            {submitLabel}
                        </Button>
                        <Button variant="ghost" asChild>
                            <Link href={admin.shops.index()}>Cancel</Link>
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
