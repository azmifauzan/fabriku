export function totalQuantity(items: Array<{ quantity: number | string }>): number {
    return items.reduce((sum, item) => sum + Number(item.quantity), 0);
}
