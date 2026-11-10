import { Head, usePage } from '@inertiajs/react';
import Heading from '@/components/heading';
import owner from '@/routes/owner';

export default function OwnerDashboard() {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Shop Owner Dashboard" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title={`Welcome, ${auth.user.name}`}
                    description="Manage your shops and their product listings."
                />
            </div>
        </>
    );
}

OwnerDashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: owner.dashboard(),
        },
    ],
};
