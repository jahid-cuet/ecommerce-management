import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

/**
 * Laravel labels the previous/next links with HTML entities such as "&laquo; Previous".
 */
function cleanLabel(label: string): string {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›').trim();
}

export default function Pagination<T>({
    paginator,
}: {
    paginator: Paginated<T>;
}) {
    if (paginator.last_page <= 1) {
        return null;
    }

    return (
        <nav
            aria-label="Pagination"
            className="flex flex-col items-center justify-between gap-3 sm:flex-row"
        >
            <p className="text-sm text-muted-foreground">
                Showing {paginator.from} to {paginator.to} of {paginator.total}
            </p>
            <div className="flex flex-wrap gap-1">
                {paginator.links.map((link, index) => {
                    const className = cn(
                        'inline-flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm',
                        link.active &&
                            'border-primary bg-primary text-primary-foreground',
                        !link.url && 'pointer-events-none opacity-50',
                    );

                    return link.url ? (
                        <Link
                            key={index}
                            href={link.url}
                            preserveScroll
                            className={className}
                            aria-current={link.active ? 'page' : undefined}
                        >
                            {cleanLabel(link.label)}
                        </Link>
                    ) : (
                        <span key={index} className={className}>
                            {cleanLabel(link.label)}
                        </span>
                    );
                })}
            </div>
        </nav>
    );
}
