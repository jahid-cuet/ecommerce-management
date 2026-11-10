import { Head } from '@inertiajs/react';
import { Users } from 'lucide-react';
import Heading from '@/components/heading';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import admin from '@/routes/admin';

type Props = {
    stats: {
        shopOwners: number;
    };
};

export default function AdminDashboard({ stats }: Props) {
    return (
        <>
            <Head title="Admin Dashboard" />
            <div className="flex flex-1 flex-col gap-4 p-4">
                <Heading
                    title="Admin Dashboard"
                    description="Manage shops, products and shop owner accounts."
                />
                <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader>
                            <CardDescription className="flex items-center gap-2">
                                <Users className="size-4" />
                                Shop owners
                            </CardDescription>
                            <CardTitle className="text-3xl">
                                {stats.shopOwners}
                            </CardTitle>
                        </CardHeader>
                    </Card>
                </div>
            </div>
        </>
    );
}

AdminDashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: admin.dashboard(),
        },
    ],
};
