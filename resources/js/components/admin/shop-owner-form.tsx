import { Form, Link } from '@inertiajs/react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import admin from '@/routes/admin';
import type { ShopOwner } from '@/types';
import type { RouteFormDefinition } from '@/wayfinder';

type Props = {
    action: RouteFormDefinition<'post' | 'put' | 'patch'>;
    shopOwner?: Pick<ShopOwner, 'name' | 'email'>;
    submitLabel: string;
};

export default function ShopOwnerForm({
    action,
    shopOwner,
    submitLabel,
}: Props) {
    const isEditing = shopOwner !== undefined;

    return (
        <Form
            {...action}
            className="max-w-xl space-y-6"
            resetOnError={['password', 'password_confirmation']}
        >
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            defaultValue={shopOwner?.name}
                            required
                            autoFocus
                            placeholder="Full name"
                        />
                        <InputError message={errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Email address</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            defaultValue={shopOwner?.email}
                            required
                            autoComplete="off"
                            placeholder="owner@example.com"
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">
                            {isEditing ? 'New password' : 'Password'}
                        </Label>
                        <PasswordInput
                            id="password"
                            name="password"
                            required={!isEditing}
                            autoComplete="new-password"
                            placeholder={
                                isEditing
                                    ? 'Leave blank to keep the current password'
                                    : 'Password'
                            }
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">
                            Confirm password
                        </Label>
                        <PasswordInput
                            id="password_confirmation"
                            name="password_confirmation"
                            required={!isEditing}
                            autoComplete="new-password"
                            placeholder="Confirm password"
                        />
                    </div>

                    <div className="flex items-center gap-2">
                        <Button type="submit" disabled={processing}>
                            {processing && <Spinner />}
                            {submitLabel}
                        </Button>
                        <Button variant="ghost" asChild>
                            <Link href={admin.shopOwners.index()}>Cancel</Link>
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
