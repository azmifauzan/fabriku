<?php

namespace App\Helpers;

class CategoryHelper
{
    /**
     * Get all enabled business categories
     */
    public static function getEnabledCategories(): array
    {
        $categories = config('business.categories', []);

        return array_filter($categories, function ($category) {
            return ($category['enabled'] ?? true) === true;
        });
    }

    /**
     * Get categories for select options
     */
    public static function getCategoryOptions(): array
    {
        $categories = self::getEnabledCategories();

        return array_map(function ($category, $key) {
            return [
                'value' => $key,
                'label' => $category['label'],
                'icon' => $category['icon'] ?? '📦',
                'description' => $category['description'] ?? '',
            ];
        }, $categories, array_keys($categories));
    }

    /**
     * Get terminology for a specific category
     */
    public static function getTerminology(string $category, string $key): string
    {
        $config = config("business.categories.{$category}", []);

        return $config['terminology'][$key] ?? ucfirst($key);
    }

    /**
     * Get all terminology for a category
     */
    public static function getAllTerminology(string $category): array
    {
        $config = config("business.categories.{$category}", []);

        return $config['terminology'] ?? [];
    }

    /**
     * Get product types for a category
     */
    public static function getProductTypes(string $category): array
    {
        $config = config("business.categories.{$category}", []);

        return $config['product_types'] ?? [];
    }

    /**
     * Get sizes for a category
     */
    public static function getSizes(string $category): array
    {
        $config = config("business.categories.{$category}", []);

        return $config['sizes'] ?? [];
    }

    /**
     * Get material types for a category
     */
    public static function getMaterialTypes(string $category): array
    {
        $config = config("business.categories.{$category}", []);

        return $config['material_types'] ?? [];
    }

    /**
     * Get material attributes for a category
     */
    public static function getMaterialAttributes(string $category): array
    {
        $config = config("business.categories.{$category}", []);

        return $config['material_attributes'] ?? [];
    }

    /**
     * Get business rules for a category
     */
    public static function getBusinessRules(string $category): array
    {
        $config = config("business.categories.{$category}", []);

        return $config['rules'] ?? [];
    }

    /**
     * Check if category tracks batch numbers
     */
    public static function tracksBatchNumber(string $category): bool
    {
        $rules = self::getBusinessRules($category);

        return $rules['track_batch_number'] ?? false;
    }

    /**
     * Check if category tracks expired dates
     */
    public static function tracksExpiredDate(string $category): bool
    {
        $rules = self::getBusinessRules($category);

        return $rules['track_expired_date'] ?? false;
    }

    /**
     * Check if category requires storage temperature
     */
    public static function requiresStorageTemp(string $category): bool
    {
        $rules = self::getBusinessRules($category);

        return $rules['require_storage_temp'] ?? false;
    }

    /**
     * Get standard waste percentage range for category
     */
    public static function getWastePercentageRange(string $category): array
    {
        $rules = self::getBusinessRules($category);

        return $rules['standard_waste_percentage'] ?? [0, 10];
    }

    /**
     * Get quality grades for category
     */
    public static function getQualityGrades(string $category): array
    {
        $rules = self::getBusinessRules($category);

        return $rules['quality_grades'] ?? [];
    }
}
