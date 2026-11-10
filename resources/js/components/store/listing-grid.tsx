import Pagination from '@/components/pagination';
import ListingCard from '@/components/store/listing-card';
import type { Paginated, StoreListing } from '@/types';

type Props = {
    listings: Paginated<StoreListing>;
    showShop?: boolean;
    emptyMessage: string;
};

export default function ListingGrid({
    listings,
    showShop,
    emptyMessage,
}: Props) {
    if (listings.data.length === 0) {
        return (
            <p className="rounded-xl border border-dashed p-10 text-center text-sm text-muted-foreground">
                {emptyMessage}
            </p>
        );
    }

    return (
        <div className="grid gap-6">
            <div className="grid grid-cols-2 gap-x-4 gap-y-8 md:grid-cols-3 lg:grid-cols-4">
                {listings.data.map((listing) => (
                    <ListingCard
                        key={listing.id}
                        listing={listing}
                        showShop={showShop}
                    />
                ))}
            </div>
            <Pagination paginator={listings} />
        </div>
    );
}
