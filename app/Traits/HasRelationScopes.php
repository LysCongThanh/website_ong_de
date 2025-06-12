<?php

namespace App\Traits;

trait HasRelationScopes
{
    public static function translationScope(?string $locale = 'vi'): callable
    {
        return fn($q) => $q->select(['id', 'entity_id', 'entity_type', 'locale', 'translations'])
            ->where('locale', $locale);
    }

    public static function policyRelations(?string $locale = 'vi'): callable
    {
        return fn($q) => $q->select([
                'id', 'name', 'description', 'content', 'type', 'is_active', 'policyable_id', 'policyable_type'
            ])
            ->where('is_active', true)
            ->with(['translations' => self::translationScope($locale)]);
    }

    public static function basePriceRelations(?string $locale = 'vi'): callable
    {
        return fn($q) => $q->select(['id', 'priceable_id', 'priceable_type', 'price_type_id', 'price', 'is_active'])
            ->where('is_active', true)
            ->with([
                'priceType' => fn($ptq) => $ptq->select(['id', 'name', 'description'])
                    ->with(['translations' => self::translationScope($locale)]),
            ]);
    }

    public static function capacityPriceRelations(?string $locale = 'vi'): callable
    {
        return fn($q) => $q->select([
                'id', 'priceable_id', 'priceable_type', 'price_type_id',
                'customer_segment_id', 'price', 'min_person', 'max_person', 'is_active'
            ])
            ->where('is_active', true)
            ->with([
                'priceType' => fn($ptq) => $ptq->select(['id', 'name', 'description'])
                    ->with(['translations' => self::translationScope($locale)]),
                'customerSegment' => fn($csq) => $csq->select(['id', 'name'])
                    ->with(['translations' => self::translationScope($locale)]),
            ]);
    }

    public static function segmentPriceRelations(?string $locale = 'vi'): callable
    {
        return fn($q) => $q->select([
                'id', 'priceable_id', 'priceable_type', 'price_type_id',
                'customer_segment_id', 'price', 'is_active'
            ])
            ->where('is_active', true)
            ->with([
                'priceType' => fn($ptq) => $ptq->select(['id', 'name', 'description'])
                    ->with(['translations' => self::translationScope($locale)]),
                'customerSegment' => fn($csq) => $csq->select(['id', 'name'])
                    ->with(['translations' => self::translationScope($locale)]),
            ]);
    }
}
