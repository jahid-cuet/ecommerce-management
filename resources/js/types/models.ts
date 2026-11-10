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

export type Product = {
    id: number;
    name: string;
    description: string | null;
    price: string;
    thumbnail_url: string | null;
};

export type ProductListItem = Pick<
    Product,
    'id' | 'name' | 'price' | 'thumbnail_url'
> & {
    creator: { name: string; role: string } | null;
    created_at: string;
};

export type ShopSummary = Pick<Shop, 'id' | 'name' | 'slug' | 'is_active'>;

export type OwnerShop = ShopSummary & {
    description?: string | null;
    listings_count: number;
};

export type Listing = {
    id: number;
    price: string;
    description: string | null;
    /** The shop's own thumbnail; null means the catalog thumbnail is shown. */
    thumbnail_url: string | null;
};

export type ListingListItem = {
    id: number;
    name: string;
    price: string;
    catalog_price: string;
    thumbnail_url: string | null;
    has_custom_details: boolean;
    has_custom_thumbnail: boolean;
};

export type CatalogProduct = Product & {
    added_by: string | null;
    listing_id: number | null;
};

export type OwnProductListItem = Pick<
    Product,
    'id' | 'name' | 'price' | 'thumbnail_url'
> & {
    listings_count: number;
};

export type StoreShop = {
    name: string;
    slug: string;
    description: string | null;
};

export type StoreShopCard = StoreShop & { listings_count: number };

/** A product as customers see it in one shop: that shop's price and image. */
export type StoreListing = {
    id: number;
    name: string;
    product_slug: string;
    price: string;
    thumbnail_url: string | null;
    shop: Pick<StoreShop, 'name' | 'slug'>;
};

export type StoreListingDetail = StoreListing & {
    description: string | null;
};
