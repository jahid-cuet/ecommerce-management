import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { RouteDefinition } from '@/wayfinder';

type Props = {
    label: string;
    value: number;
    hint?: string;
    icon: LucideIcon;
    href: RouteDefinition<'get'>;
};

/**
 * A dashboard number that links to the page it counts.
 */
export default function StatCard({
    label,
    value,
    hint,
    icon: Icon,
    href,
}: Props) {
    return (
        <Link href={href}>
            <Card className="h-full transition-colors hover:bg-muted/50">
                <CardHeader>
                    <CardDescription className="flex items-center gap-2">
                        <Icon className="size-4" />
                        {label}
                    </CardDescription>
                    <CardTitle className="text-3xl">{value}</CardTitle>
                    {hint && (
                        <p className="text-xs text-muted-foreground">{hint}</p>
                    )}
                </CardHeader>
            </Card>
        </Link>
    );
}
