const priceFormatter = new Intl.NumberFormat('en-BD', {
    style: 'currency',
    currency: 'BDT',
    minimumFractionDigits: 2,
});

/**
 * Formats a price such as "1250.00" as "BDT 1,250.00".
 */
export function formatPrice(price: string | number): string {
    return priceFormatter.format(Number(price));
}
