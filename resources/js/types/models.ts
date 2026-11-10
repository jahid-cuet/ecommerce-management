export type ShopOwner = {
    id: number;
    name: string;
    email: string;
    shops_count: number;
    created_at: string;
};

export type OwnerOption = Pick<ShopOwner, 'id' | 'name' | 'email'>;

export type Shop = {
    id: number;
    owner_id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
};

export type ShopListItem = Pick<Shop, 'id' | 'name' | 'slug' | 'is_active'> & {
    owner: OwnerOption;
    created_at: string;
};
