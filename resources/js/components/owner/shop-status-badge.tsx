import { Badge } from '@/components/ui/badge';

/**
 * Shows whether customers can currently see the shop.
 */
export default function ShopStatusBadge({ active }: { active: boolean }) {
    return (
        <Badge variant={active ? 'default' : 'secondary'}>
            {active ? 'Active' : 'Inactive'}
        </Badge>
    );
}
