import { clsx, type ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/** Formats a number with Indonesian thousand separators (e.g. 1234 -> "1.234"). */
export function formatNumber(value: number | string | null | undefined, decimals: number = 0): string {
    const num = typeof value === 'string' ? parseFloat(value) : (value ?? 0);
    if (Number.isNaN(num)) return '0';
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: decimals,
    }).format(num);
}
