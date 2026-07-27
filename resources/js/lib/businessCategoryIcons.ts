import { ChefHat, Home, Palette, Scissors, Sparkles, Store, Wrench, type LucideIcon } from 'lucide-vue-next';

export const businessCategoryIcons: Record<string, LucideIcon> = {
    garment: Scissors,
    food: ChefHat,
    craft: Palette,
    cosmetic: Sparkles,
    retail: Store,
    homemade: Home,
    service: Wrench,
};
